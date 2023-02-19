// ------------step-wizard-------------

const maxFeatures = '50'
const help_className = "help-block";

// const BASEURL = 'http://localhost:8000/emergenze/'

const BASEURL = config.BASE_URL

const STRADARIOURL = 'https://mappe.comune.genova.it/geoserver/wfs'
  + '?service=WFS&version=1.1.0&request=GetFeature&outputFormat=application%2Fjson&typeName=SITGEO:V_ASTE_STRADALI_TOPONIMO_SUB&sortBy=ID&srsName=EPSG:4326&startIndex=0';

const CIVICOURL    = 'https://mappe.comune.genova.it/geoserver/wfs'
  + '?service=WFS&version=1.1.0&request=GetFeature&outputFormat=application%2Fjson&typeName=SITGEO:CIVICI_COD_TOPON&sortBy=NUMERO&srsName=EPSG:4326&startIndex=0'

// let STRADARIO_PAYLOAD = {
//     service: "WFS",
//     version: "1.1.0",
//     request: "GetFeature",
//     outputFormat: "application/json",
//     maxFeatures: "20",
//     typeName: "SITGEO:V_ASTE_STRADALI_TOPONIMO_SUB",
//     sortBy: "ID",
//     srsName: "EPSG:4326",
//     startIndex: "0"
// }

$(document).ready(function () {

    [].forEach.call(document.querySelectorAll('input[data-form]'), (el)=>{
        el.addEventListener('input', ()=>{
            const binded = document.querySelector(`input[data-form='${el.getAttribute("data-form")}'][name='${el.getAttribute("name")}'][data-binded]`);
            binded.value = el.value;
        });
    });

    [].forEach.call(document.querySelectorAll('select[data-form]'), (el)=>{
        el.addEventListener('change', ()=>{
            const binded = document.querySelector(`input[data-form='${el.getAttribute("data-form")}'][name='${el.getAttribute("name")}'][data-binded]`);
            binded.value = el.value;
        });
    });

    form1_setup();
    form2_setup();
    form3_setup();
    subscribe_setup();
    wiz_setup();

});

window.events = {};

function collectPayload(form, key, saveData) {
    const inputs = form.querySelectorAll(`input[data-form='${key}'], select[data-form='${key}']`);
    const formdata = new FormData();
    [].forEach.call(inputs, (el)=>{
        formdata.append(el.name, el.value);
    });
    if (!saveData) {
        formdata.append('rollback', 'true');
    };
    const requestOptions = {
      method: 'POST',
      body: formdata,
      redirect: 'follow'
    };
    return requestOptions;
};

function form1_setup() {

    document.getElementById('internoCivico').addEventListener('input', function (e) {
        document.getElementById('interno').value = e.target.value;
    });

    $('#toponimo').autoComplete({
        resolver: 'custom',
        noResultsText: 'Nessun risultato trovato!',
        bootstrapVersion: '3',
        events: {
            // formatResult: function (item) {
            //     console.log(item);
            //     return {text: item}},
            search: function (qry, callback) {
              var myHeaders = new Headers();
              // myHeaders.append("Cookie", "GS_FLOW_CONTROL=GS_CFLOW_-5bffe80a:18557f2f12c:-4e9f");

              var requestOptions = {
                  method: 'GET',
                  headers: myHeaders,
                  redirect: 'follow'
              };

              let url = STRADARIOURL + `&cql_filter=(NOMEVIA+ILIKE+%27%25${qry}%25%27)&maxFeatures=${maxFeatures}`;

              // var url = new URL(STRADARIOURL)
              // url.search = new URLSearchParams({
              //     ...STRADARIO_PAYLOAD,
              //     cql_filter: `(NOMEVIA+ILIKE+"%${qry}%")`
              // }).toString();

              fetch(url, requestOptions)
              .then(response => response.json())
              .then(result => {
                  let options = result.features.map((fc) => ({
                      text: fc.properties.NOMEVIA,
                      properties: {
                          codice: fc.properties.COD_STRADA
                      }
                  }));
                  callback(options);
              })
              // .catch(error => console.log('error', error));
                // let's do a custom ajax call
                // $.ajax(
                //     url
                // ).done(function (res) {
                //     console.log(res);
                //     const out = res.features.map((fc, idx) => fc.properties.NOMEVIA);
                //     callback(out)
                // });
            }
        }
    }).on("autocomplete.select ", (evt, item)=>{
        document.getElementById('civico').value = "";
        window.codiceStrada = item.properties.codice;
        document.getElementById("civico").removeAttribute("disabled");
    });

    $('#civico').autoComplete({
        minLength: 0,
        resolver: 'custom',
        noResultsText: 'Nessun risultato trovato!',
        bootstrapVersion: '3',
        events: {
            // formatResult: function (item) {
            //     console.log(item);
            //     return {text: item}},
            search: function (qry, callback) {
              var myHeaders = new Headers();
              // myHeaders.append("Cookie", "GS_FLOW_CONTROL=GS_CFLOW_-5bffe80a:18557f2f12c:-4e9f");

              var requestOptions = {
                  method: 'GET',
                  headers: myHeaders,
                  redirect: 'follow'
              };

              // let url = CIVICOURL + `&cql_filter=(TESTO+ILIKE+%27%251%25%27)+AND+COD_STRADA+%3D+%2738000%27`;
              // let url = CIVICOURL + `&cql_filter=(TESTO+ILIKE+%27%25${qry||''}%25%27)+AND+DESVIA+%3D+%27${item.text}%27&maxFeatures=${maxFeatures}`;
              let url = CIVICOURL + `&cql_filter=(TESTO+ILIKE+%27%25${qry||''}%25%27)+AND+COD_STRADA+%3D+%27${window.codiceStrada}%27&maxFeatures=${maxFeatures}`;

              // var url = new URL(STRADARIOURL)
              // url.search = new URLSearchParams({
              //     ...STRADARIO_PAYLOAD,
              //     cql_filter: `(NOMEVIA+ILIKE+"%${qry}%")`
              // }).toString();

              fetch(url, requestOptions)
              .then(response => response.json())
              .then(result => {
                  let options = result.features.map((fc) => ({
                      text: fc.properties.TESTO,
                      properties: {
                          idVia: fc.properties.COD_STRADA,
                          colore: fc.properties.COLORE,
                          esponente: fc.properties.LETTERA,
                          numeroCivico: fc.properties.NUMERO,
                          indirizzoCompleto: fc.properties.MACHINE_LAST_UPD
                      }

                  }));
                  callback(options);
              })
              // .catch(error => console.log('error', error));
                // let's do a custom ajax call
                // $.ajax(
                //     url
                // ).done(function (res) {
                //     console.log(res);
                //     const out = res.features.map((fc, idx) => fc.properties.NOMEVIA);
                //     callback(out)
                // });
            }
        }
    }).on("autocomplete.select ", (evt, item)=>{
        // console.log(item.properties);
        for ( const [kk, vv] of Object.entries(item.properties) ) {
            document.getElementById(kk).value = vv;
        };
        document.getElementById("submitStep1").removeAttribute('disabled');
    });

};

function validate(results, container) {
    let successes = 0
    for (const result of results) {
        switch (result.status) {
            case 200:
                successes += 1;
                break;
            case undefined:
                successes += 1;
                break;
            case 400:
                let successes_ = 0
                for ( const [kk, vv] of Object.entries(result.errors) ) {
                    // const el = document.getElementById(kk);
                    const el = container.querySelector(`input[name='${kk}'], select[name='${kk}']`)
                    if (el) {
                        const err = document.createElement('span');
                        err.classList.add(help_className);
                        err.append(vv)
                        el.parentElement.appendChild(err);
                        el.parentElement.parentElement.classList.remove('has-warning');
                        el.parentElement.parentElement.classList.add('has-error');
                    } else {
                        successes_ += 1;
                    };
                };
                if (Object.keys(result.errors).length==successes_) {
                    successes += 1;
                };
                break;
          };
    };
    return (results.length==successes);
};

function form2_setup() {

    const requestOptions = {
        method: 'GET',
        redirect: 'follow'
    };

    fetch(BASEURL+"lingue", requestOptions)
        .then(response => response.json())
        .then(result => {
            // const form2 = document.getElementById('step2');
            let linguaSelect = document.getElementById('linguaNoItalia');
            // console.log(result);
            options = result.map(lang => {
                let option = document.createElement('option');
                option.setAttribute('value', lang.idLingua);
                option.append(lang.descrizione.toUpperCase())
                return option;
            });
            linguaSelect.append(...options);
            // console.log(linguaSelect);
        })
        .catch(error => console.log('error', error));


    function checkSubscribe () {
        const form = document.getElementById("step2");

        return Promise.all([
            fetch(BASEURL+"utente", collectPayload(form, 'utente')).then(response => response.json()),
            fetch(BASEURL+"telefono", collectPayload(form, 'contatto')).then(response => response.json()),
        ]).then(results => validate(results, form))
    };

    const elemId = 'submitStep2'
    window.events[elemId] = checkSubscribe;

};

function form3_setup() {

    const posizione = document.getElementById('posizione');
    const vulnerabilita = document.getElementById('vulnerabilita');
    const amministratore = document.getElementById('amministratore');
    const proprietario = document.getElementById('proprietario');

    function toggleEnable() {

        let btn = document.getElementById('submitStep3');

        if (
            (vulnerabilita.value=='PERSONALE' || ['STRADA', 'SOTTOSTRADA'].includes(posizione.value))
            && amministratore.value && proprietario.value
        ) {
            btn.removeAttribute('disabled');
        } else {
            btn.setAttribute('disabled', true);
        };
    };

    posizione.addEventListener('change', toggleEnable);
    vulnerabilita.addEventListener('change', toggleEnable);
    amministratore.addEventListener('input', toggleEnable);
    proprietario.addEventListener('input', toggleEnable);

    function checkSubscribeStep3 () {
        const form = document.getElementById("step3");

        return Promise.all([
            fetch(BASEURL+"civico", collectPayload(form, 'recapito')).then(response => response.json()),
            fetch(BASEURL+"componente", collectPayload(form, 'nucleo')).then(response => response.json()),
          ]).then(results => validate(results, form))
    };

    const elemId = 'submitStep3'
    window.events[elemId] = checkSubscribeStep3;

};

function subscribe_setup() {

    function submitSubscribe () {
        const form = document.getElementById("step4");
        let utenteData = collectPayload(form, 'utente', true);
        utenteData.body.append('iscrizione', 'Protezione civile');
        return Promise.all([
            fetch(BASEURL+"utente", utenteData).then(response => response.json()),
            fetch(BASEURL+"civico", collectPayload(form, 'recapito', true)).then(response => response.json()),
        ]).then(results1 => {
            // TODO

            const [utente, civico] = results1;

            let contattoPayload = collectPayload(form, 'contatto', true);
            let componentePayload = collectPayload(form, 'nucleo', true)

            // console.log(civico)
            contattoPayload.body.append('idUtente', utente.id);
            componentePayload.body.append('idUtente', utente.id);
            componentePayload.body.append('idCivico', civico.id);

            // console.log(contattoPayload);
            // console.log(componentePayload);

            // TODO: await next call
            Promise.all([
                fetch(BASEURL+"telefono", contattoPayload),
                fetch(BASEURL+"componente", componentePayload)
            ]).then(results2 => {
                const [telResponse, roleResponse] = results2;
                console.log(telResponse, roleResponse);
                if (telResponse.status==200 && roleResponse.status==200) {
                    document.getElementById("successPanel").removeAttribute("hidden");
                } else {
                    document.getElementById("failPanel").removeAttribute("hidden");
                };
                // switch ([telResponse.status, roleResponse.status]) {
                //     case ([200, 200]):
                //         document.getElementById("successPanel").removeAttribute("hidden");
                //         break;
                //     default:
                //         document.getElementById("failPanel").removeAttribute("hidden");
                //         break;
                // };
            });

            return true
        });
    }

    const elemId = 'submitSubscribe'
    window.events[elemId] = submitSubscribe;
};

function wiz_setup () {
    $('.nav-tabs > li a[title]').tooltip();

    //Wizard
    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {

        var $target = $(e.target);

        if ($target.parent().hasClass('disabled')) {
            return false;
        }
    });

    function goOn() {
        var $active = $('.wizard .nav-tabs li.active');
        $active.next().removeClass('disabled');
        nextTab($active);
    };

    $(".skip-step").click(function (e) {goOn()});

    $(".next-step").click(function (e) {

        if (e.target.id && e.target.id in window.events) {
            e.target.parentElement.parentElement.parentElement.querySelectorAll("input, select").forEach(el => {
                el.parentElement.querySelectorAll(`.${help_className}`).forEach(el => el.remove());
                el.parentElement.classList.remove('has-error');
                el.parentElement.classList.remove('has-warning');
                el.parentElement.classList.add('has-success');
                el.parentElement.parentElement.classList.remove('has-warning');
                el.parentElement.parentElement.classList.remove('has-error');
                el.parentElement.parentElement.classList.add('has-success');

            });
            window.events[e.target.id]().then(canIGoOn => {
                if (canIGoOn) goOn();
            });
        } else {
            goOn();
        };

    });

    $(".prev-step").click(function (e) {

        var $active = $('.wizard .nav-tabs li.active');
        prevTab($active);

    });
};

function nextTab(elem) {
    $(elem).next().find('a[data-toggle="tab"]').click();
}
function prevTab(elem) {
    $(elem).prev().find('a[data-toggle="tab"]').click();
}
