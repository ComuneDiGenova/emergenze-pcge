// ------------step-wizard-------------

const maxFeatures = '50'
const help_className = "help-block";

const BASEURL = 'http://localhost:8000/emergenze/'

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
    form1_setup();
    form2_setup();
    wiz_setup();

});

window.events = {};

function form1_setup() {
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
                  let options = result.features.map((fc) => fc.properties.NOMEVIA);
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
        window.address = item;
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

              const desvia = window.address;

              // let url = CIVICOURL + `&cql_filter=(TESTO+ILIKE+%27%251%25%27)+AND+COD_STRADA+%3D+%2738000%27`;
              let url = CIVICOURL + `&cql_filter=(TESTO+ILIKE+%27%25${qry||''}%25%27)+AND+DESVIA+%3D+%27${desvia}%27&maxFeatures=${maxFeatures}`;

              // var url = new URL(STRADARIOURL)
              // url.search = new URLSearchParams({
              //     ...STRADARIO_PAYLOAD,
              //     cql_filter: `(NOMEVIA+ILIKE+"%${qry}%")`
              // }).toString();

              fetch(url, requestOptions)
              .then(response => response.json())
              .then(result => {
                  let options = result.features.map((fc) => fc.properties.TESTO);
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
    });


};

function form2_setup() {

    var requestOptions = {
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

    function checkSubscribe (callback) {
        const form = document.getElementById("step2");
        const inputs = form.querySelectorAll("input[data-form='utente'], select[data-form='utente']");

        var formdata = new FormData();
        [].forEach.call(inputs, (el)=>{
            formdata.append(el.name, el.value);
        });

        var requestOptions = {
          method: 'POST',
          body: formdata,
          redirect: 'follow'
        };

        return Promise.all([
            fetch(BASEURL+"utente", requestOptions).then(response => response.json())
          ]).then(results => {
            const result = results[0];
            switch (result.status) {
                case 200:
                    callback();
                    break;
                case 400:
                    for ( const [kk, vv] of Object.entries(result.errors) ) {
                        const el = document.getElementById(kk);

                        // el.parentElement.querySelectorAll(`.${help_className}`).forEach(el => el.remove());
                        const err = document.createElement('span');
                        err.classList.add(help_className);
                        err.append(vv)
                        el.parentElement.appendChild(err);
                        el.parentElement.parentElement.classList.remove('has-warning');
                        el.parentElement.parentElement.classList.add('has-error');
                    };
                    // alert(result.detail);

                    break;
            }
        })

    };

    const elemId = 'submitStep2'
    window.events[elemId] = checkSubscribe;

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

    $(".next-step").click(function (e) {

        function goOn() {
            var $active = $('.wizard .nav-tabs li.active');
            $active.next().removeClass('disabled');
            nextTab($active);
        };

        if (e.target.id) {
            e.target.parentElement.parentElement.parentElement.querySelectorAll("input, select").forEach(el => {
                el.parentElement.querySelectorAll(`.${help_className}`).forEach(el => el.remove());
                el.parentElement.classList.remove('has-error');
                el.parentElement.classList.remove('has-warning');
                el.parentElement.classList.add('has-success');
                el.parentElement.parentElement.classList.remove('has-warning');
                el.parentElement.parentElement.classList.remove('has-error');
                el.parentElement.parentElement.classList.add('has-success');

            });
            window.events[e.target.id](goOn)

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
