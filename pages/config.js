let config;
if ( window.location.host == 'emergenze.comune.genova.it' ) {
    config = {
        BASE_URL: "https://emergenze-api.comune.genova.it/emergenze/",
        LOCAL_URL: "http://localhost:8000/emergenze/",
        DB_URL: "https://emergenze-api.comune.genova.it/emergenze/",
    };
} else {
    config = {
        BASE_URL: "https://emergenze-apit.comune.genova.it/emergenze/",
        LOCAL_URL: "http://localhost:8000/emergenze/",
        DB_URL: "https://emergenze-apit.comune.genova.it/emergenze/",
    };
};
