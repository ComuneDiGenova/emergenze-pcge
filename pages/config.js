if ( $_SERVER['HTTP_HOST'] == 'vm-lxprotcivemet.comune.genova.it' ) {
    const config = {
        BASE_URL: "https://emergenze-api.comune.genova.it/emergenze/",
        LOCAL_URL: "http://localhost:8000/emergenze/",
        DB_URL: "https://emergenze-api.comune.genova.it/emergenze/",
    };
} else {
    const config = {
        BASE_URL: "https://emergenze-apit.comune.genova.it/emergenze/",
        LOCAL_URL: "http://localhost:8000/emergenze/",
        DB_URL: "https://emergenze-apit.comune.genova.it/emergenze/",
    };
}


