# -*- coding: utf-8 -*-

class Form(object):
    """
    Usage in py4web controller:
       def index():
           form = Form(db.thing, record=1)
           if form.accepted: ...
           elif form.errors: ...
           else: ...
           return dict(form=form)
    Arguments:
    :param table: a DAL table or a list of fields (equivalent to old SQLFORM.factory)
    # :param record: a DAL record or record id
    :param readonly: set to True to make a readonly form
    :param deletable: set to False to disallow deletion of record
    # :param noncreate: make sure when you use a form with a list of fields that does not contain the id field, does not always render the create form.
    # :param formstyle: a function that renders the form using helpers (FormStyleDefault)
    :param dbio: set to False to prevent any DB writes
    # :param keep_values: if set to true, it remembers the values of the previously submitted form
    # :param form_name: the optional name of this form
    # :param csrf_session: if None, no csrf token is added.  If a session, then a CSRF token is added and verified.
    # :param lifespan: lifespan of CSRF token in seconds, to limit form validity.
    # :param signing_info: information that should not change between when the CSRF token is signed and
    #     verified.  This information is not leaked to the form.  For instance, if you wish to verify
    #     that the identity of the logged in user has not changed, you can do as below.
    #     signing_info = session.get('user', {}).get('id', '')
    #     The content of the field should be convertible to a string via json.
    """

    def __init__(
        self,
        table,
        # record=None,
        readonly=False,
        deletable=True,
        # noncreate=True,
        # formstyle=FormStyleDefault,
        dbio=True,
        # keep_values=False,
        # form_name=None,
        hidden=None,
        validation=None,
        # csrf_session=None,
        csrf_protection=True,
        # lifespan=None,
        # signing_info=None,
        # submit_value="Submit",
        show_id=True,
        **kwargs
    ):
        self.param = Param(
            formstyle=formstyle,
            hidden=hidden,
            submit_value=submit_value,
            sidecar=[],
        )

        if isinstance(table, list):
            dbio = False
            # Mimic a table from a list of fields without calling define_table
            form_name = form_name or "none"
            for field in table:
                field.tablename = getattr(field, "tablename", form_name)

        if isinstance(record, (int, str)):
            record_id = int(str(record))
            self.record = table[record_id]
            if not self.record:
                raise HTTP(404)
        else:
            self.record = record

        # computed from input and not changed
        self.table = table
        self.deletable = self.record and deletable and not readonly
        self.dbio = dbio
        self.keep_values = True if keep_values or self.record else False
        self.form_name = form_name or table._tablename
        self.csrf_session = csrf_session
        self.signing_info = signing_info
        self.validation = validation
        self.lifespan = lifespan
        self.csrf_protection = csrf_protection
        self.show_id = show_id
        # initialized and can change
        self.vars = {}
        self.errors = {}
        self.readonly = readonly
        self.noncreate = noncreate
        self.submitted = False
        self.deleted = False
        self.accepted = False
        self.formkey = None
        self.cached_helper = None
        self.action = None

        self.kwargs = kwargs if kwargs else {}

        if self.record:
            self.vars = self._read_vars_from_record(table)
        if not readonly and request.method != "GET":
            post_vars = request.POST
            form_vars = copy.deepcopy(request.forms)
            for k in form_vars:
                self.vars[k] = form_vars[k]
            self.submitted = True
            process = False

            # We only a process a form if it is POST and the formkey matches (correct formname and crsf)
            # Notice: we never expose the crsf uuid, we only use to sign the form uuid
            if request.method == "POST":
                if not self.csrf_protection or self._verify_form(post_vars):
                    process = True
            if process:
                record_id = self.record and self.record.get("id")
                if not post_vars.get("_delete"):
                    validated_vars = {}
                    uploaded_files = []
                    for field in self.table:
                        if field.writable and field.type != "id":
                            original_value = post_vars.get(field.name)
                            if isinstance(original_value, list):
                                if len(original_value) == 1:
                                    original_value = original_value[0]

                                elif len(original_value) == 0:
                                    original_value = None
                            if field.type.startswith("list:") and isinstance(
                                original_value, str
                            ):
                                try:
                                    original_value = json.loads(original_value or "[]")
                                except json.decoder.JSONDecodeError:
                                    # this happens if posting a single value
                                    pass
                            (value, error) = field.validate(original_value, record_id)
                            if field.type == "password" and record_id and value is None:
                                continue
                            if field.type == "upload":
                                value = request.files.get(field.name)
                                print(str(value)[:100])
                                delete = post_vars.get("_delete_" + field.name)
                                if value is not None:
                                    if field.uploadfolder:
                                        uploaded_files.append(tuple((field, value)))
                                    validated_vars[field.name] = value
                                elif self.record:
                                    if not delete:
                                        validated_vars[field.name] = self.record.get(
                                            field.name
                                        )
                                    else:
                                        validated_vars[field.name] = value = None
                            elif field.type == "boolean":
                                validated_vars[field.name] = value is not None
                            else:
                                validated_vars[field.name] = value
                            if error:
                                self.errors[field.name] = error
                    self.vars.update(validated_vars)
                    if validation:
                        validation(self)
                    if self.record and dbio:
                        self.vars["id"] = self.record.id
                    if not self.errors:
                        for file in uploaded_files:
                            field, value = file
                            value = field.store(
                                value.file, value.filename, field.uploadfolder
                            )
                            if value is not None:
                                validated_vars[field.name] = value
                        self.accepted = True
                        self.vars.update(validated_vars)
                        if dbio:
                            self.update_or_insert(validated_vars)
                elif dbio:
                    self.deleted = True
                    self.record.delete_record()
            elif self.record:
                # This form should not be processed.  We return the same as for GET.
                self.vars = self._read_vars_from_record(table)
        if self.csrf_protection:
            self._sign_form()

    def _read_vars_from_record(self, table):
        if isinstance(table, list):
            # The table is just a list of fields.
            return {field.name: self.record.get(field.name) for field in table}
        else:
            return {
                name: table[name].formatter(self.record[name])
                for name in table.fields
                if name in self.record
            }

    def _make_key(self):
        if self.csrf_session is not None:
            key = str(uuid.uuid1())
            self.csrf_session["_formkey"] = key
        else:
            key = str(uuid.uuid1())
            response.set_cookie("_formkey", key, same_site="Strict")
        return key

    def _get_key(self):
        if self.csrf_session is not None:
            key = self.csrf_session.get("_formkey")
        else:
            key = request.get_cookie("_formkey")
        return key

    def _sign_form(self):
        """Signs the form, for csrf"""
        # Adds a form key.  First get the signing key from the session.
        payload = {"ts": str(time.time())}
        if self.lifespan is not None:
            payload["exp"] = time.time() + self.lifespan
        key = self._get_key() or self._make_key()
        self.formkey = to_native(jwt.encode(payload, key, algorithm="HS256"))

    def _verify_form(self, post_vars):
        """Verifies the csrf signature and form name."""
        if post_vars.get("_formname") != self.form_name:
            return False
        token = post_vars.get("_formkey")
        key = self._get_key()
        if not key:
            return False
        try:
            jwt.decode(token, key, algorithms=["HS256"])
            return True
        except:
            return False

    def update_or_insert(self, validated_vars):
        if self.record:
            self.record.update_record(**validated_vars)
        else:
            # warning, should we really insert if record
            self.vars["id"] = self.table.insert(**validated_vars)

    def clear(self):
        self.errors.clear()
        if not self.record and not self.keep_values:
            self.vars.clear()
            for field in self.table:
                self.vars[field.name] = (
                    field.default() if callable(field.default) else field.default
                )

    def helper(self):
        if self.accepted:
            self.clear()
        if not self.cached_helper:
            helper = self.param.formstyle(
                self.table,
                self.vars,
                self.errors,
                self.readonly,
                self.deletable,
                self.noncreate,
                show_id=self.show_id,
                kwargs=self.kwargs,
            )
            for item in self.param.sidecar:
                helper["form"][-1][-1].append(item)

                button_attributes = item.attributes
                button_attributes["_label"] = item.children[0]
                helper["json_controls"]["form_buttons"] += [button_attributes]

            if self.action:
                helper["form"]["_action"] = self.action

            if self.param.submit_value:
                helper["controls"]["submit"]["_value"] = self.param.submit_value

            if self.form_name:
                helper["controls"]["hidden_widgets"]["formname"] = INPUT(
                    _type="hidden", _name="_formname", _value=self.form_name
                )
                helper["form"].append(helper["controls"]["hidden_widgets"]["formname"])

                helper["json_controls"]["form_values"]["_formname"] = self.form_name

            if self.formkey:
                helper["controls"]["hidden_widgets"]["formkey"] = INPUT(
                    _type="hidden", _name="_formkey", _value=self.formkey
                )
                helper["form"].append(helper["controls"]["hidden_widgets"]["formkey"])

                helper["json_controls"]["form_values"]["_formkey"] = self.formkey

            for key in self.param.hidden or {}:
                helper["controls"]["hidden_widgets"][key] = INPUT(
                    _type="hidden", _name=key, _value=self.param.hidden[key]
                )
                helper["form"].append(helper["controls"]["hidden_widgets"][key])

            helper["controls"]["begin"] = XML(
                "".join(
                    str(helper["controls"]["begin"])
                    + str(helper["controls"]["hidden_widgets"][hidden_field])
                    for hidden_field in helper["controls"]["hidden_widgets"]
                )
            )
            self.cached_helper = helper

        return self.cached_helper

    @staticmethod
    def is_image(value):
        """
        Tries to check if the filename provided references to an image
        Checking is based on filename extension. Currently recognized:
           gif, png, jp(e)g, bmp
        Args:
            value: filename
        """
        if value:
            (_, extension) = os.path.splitext(value)
            if extension in [".gif", ".png", ".jpg", ".jpeg", ".bmp"]:
                return True
        return False

    @property
    def custom(self):
        return self.helper()["controls"]

    @property
    def structure(self):
        return self.helper()["form"]

    def as_json(self):
        return self.helper()["json_controls"]

    def xml(self):
        return self.structure.xml()

    def __str__(self):
        return self.xml()
