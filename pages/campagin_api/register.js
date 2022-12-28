// ------------step-wizard-------------

const BASEURL = 'http://localhost:8000/emergenze/'
const STRADARIOURL = 'https://mappe.comune.genova.it/geoserver/wfs'
  + '?service=WFS&version=1.1.0&request=GetFeature&outputFormat=application%2Fjson&maxFeatures=20&typeName=SITGEO:V_ASTE_STRADALI_TOPONIMO_SUB&sortBy=ID&srsName=EPSG:4326&startIndex=0';

let STRADARIO_PAYLOAD = {
    service: "WFS",
    version: "1.1.0",
    request: "GetFeature",
    outputFormat: "application/json",
    maxFeatures: "20",
    typeName: "SITGEO:V_ASTE_STRADALI_TOPONIMO_SUB",
    sortBy: "ID",
    srsName: "EPSG:4326",
    startIndex: "0"
}

$(document).ready(function () {
    form1_setup();
    form2_setup();
    wiz_setup();

});

function form1_setup() {
    $('#indirizzoCompleto').autoComplete({
        resolver: 'custom',
        noResultsText: 'Nessun risultato trovato!',
        bootstrapVersion: '3',
        events: {
            // formatResult: function (item) {
            //     console.log(item);
            //     return {text: item}},
            search: function (qry, callback) {
              var myHeaders = new Headers();
              myHeaders.append("Cookie", "GS_FLOW_CONTROL=GS_CFLOW_-5bffe80a:18557f2f12c:-4e9f");

              var requestOptions = {
                  method: 'GET',
                  headers: myHeaders,
                  redirect: 'follow'
              };

              let url = STRADARIOURL + `&cql_filter=(NOMEVIA+ILIKE+%27%25${qry}%25%27)`;

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

        var $active = $('.wizard .nav-tabs li.active');
        $active.next().removeClass('disabled');
        nextTab($active);

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
