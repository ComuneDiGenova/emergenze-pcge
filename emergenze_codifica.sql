--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.22
-- Dumped by pg_dump version 11.12

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: tipo_allerta; Type: TABLE; Schema: eventi; Owner: -
--

CREATE TABLE eventi.tipo_allerta (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL,
    rgb_hex character varying
);


--
-- Name: tipo_allerta_id_seq; Type: SEQUENCE; Schema: eventi; Owner: -
--

CREATE SEQUENCE eventi.tipo_allerta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipo_allerta_id_seq; Type: SEQUENCE OWNED BY; Schema: eventi; Owner: -
--

ALTER SEQUENCE eventi.tipo_allerta_id_seq OWNED BY eventi.tipo_allerta.id;


--
-- Name: tipo_evento; Type: TABLE; Schema: eventi; Owner: -
--

CREATE TABLE eventi.tipo_evento (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL,
    notifiche boolean DEFAULT true NOT NULL
);


--
-- Name: tipo_evento_id_seq; Type: SEQUENCE; Schema: eventi; Owner: -
--

CREATE SEQUENCE eventi.tipo_evento_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipo_evento_id_seq; Type: SEQUENCE OWNED BY; Schema: eventi; Owner: -
--

ALTER SEQUENCE eventi.tipo_evento_id_seq OWNED BY eventi.tipo_evento.id;


--
-- Name: tipo_foc; Type: TABLE; Schema: eventi; Owner: -
--

CREATE TABLE eventi.tipo_foc (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL,
    rgb_hex character varying
);


--
-- Name: tipo_foc_id_seq; Type: SEQUENCE; Schema: eventi; Owner: -
--

CREATE SEQUENCE eventi.tipo_foc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipo_foc_id_seq; Type: SEQUENCE OWNED BY; Schema: eventi; Owner: -
--

ALTER SEQUENCE eventi.tipo_foc_id_seq OWNED BY eventi.tipo_foc.id;


--
-- Name: tipo_idrometri_arpa; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.tipo_idrometri_arpa (
    id integer NOT NULL,
    alt integer,
    imgpath character varying,
    increment integer,
    lat double precision,
    lon double precision,
    municipality character varying,
    name character varying,
    otherhtml character varying,
    refdate timestamp with time zone,
    shortcode character varying,
    stationid integer,
    updatedatetime character varying,
    value double precision,
    geom public.geometry(PointZ,7791)
);


--
-- Name: tipo_idrometri_arpa_id_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.tipo_idrometri_arpa_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipo_idrometri_arpa_id_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.tipo_idrometri_arpa_id_seq OWNED BY geodb.tipo_idrometri_arpa.id;


--
-- Name: tipo_idrometri_comune; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.tipo_idrometri_comune (
    id character varying NOT NULL,
    nome character varying NOT NULL,
    first_rec timestamp without time zone,
    last_rec timestamp without time zone,
    usato boolean,
    lat double precision,
    lon double precision,
    geom public.geometry(PointZ,7791),
    doppione_arpa boolean DEFAULT false NOT NULL
);


--
-- Name: tipo_lettura_mire; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.tipo_lettura_mire (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL,
    rgb_hex character varying
);


--
-- Name: tipo_lettura_mire_id_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.tipo_lettura_mire_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipo_lettura_mire_id_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.tipo_lettura_mire_id_seq OWNED BY geodb.tipo_lettura_mire.id;


--
-- Name: tipo_criticita; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.tipo_criticita (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: tipo_criticita_id_seq; Type: SEQUENCE; Schema: segnalazioni; Owner: -
--

CREATE SEQUENCE segnalazioni.tipo_criticita_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipo_criticita_id_seq; Type: SEQUENCE OWNED BY; Schema: segnalazioni; Owner: -
--

ALTER SEQUENCE segnalazioni.tipo_criticita_id_seq OWNED BY segnalazioni.tipo_criticita.id;


--
-- Name: tipo_oggetti_rischio; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.tipo_oggetti_rischio (
    id integer NOT NULL,
    nome_tabella character varying NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL,
    campo_identificativo character varying NOT NULL,
    elenco_elementi_segnalazione boolean DEFAULT true NOT NULL
);


--
-- Name: TABLE tipo_oggetti_rischio; Type: COMMENT; Schema: segnalazioni; Owner: -
--

COMMENT ON TABLE segnalazioni.tipo_oggetti_rischio IS 'Gli oggetti devono essere precaricati dal DB Oracle al DB Emergenze servendosi della tabella geodb.m_tables. 

Le colonne vanno compilate manualmente. Si potrebbe migliorare servendosi delle tabelle pg_catalog';


--
-- Name: tipo_oggetti_rischio_id_seq; Type: SEQUENCE; Schema: segnalazioni; Owner: -
--

CREATE SEQUENCE segnalazioni.tipo_oggetti_rischio_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipo_oggetti_rischio_id_seq; Type: SEQUENCE OWNED BY; Schema: segnalazioni; Owner: -
--

ALTER SEQUENCE segnalazioni.tipo_oggetti_rischio_id_seq OWNED BY segnalazioni.tipo_oggetti_rischio.id;


--
-- Name: tipo_provvedimenti_cautelari; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.tipo_provvedimenti_cautelari (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: tipo_segnalanti; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.tipo_segnalanti (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: TABLE tipo_segnalanti; Type: COMMENT; Schema: segnalazioni; Owner: -
--

COMMENT ON TABLE segnalazioni.tipo_segnalanti IS '! altro corrispondere sempre al max id ';


--
-- Name: tipo_segnalanti_id_seq; Type: SEQUENCE; Schema: segnalazioni; Owner: -
--

CREATE SEQUENCE segnalazioni.tipo_segnalanti_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipo_segnalanti_id_seq; Type: SEQUENCE OWNED BY; Schema: segnalazioni; Owner: -
--

ALTER SEQUENCE segnalazioni.tipo_segnalanti_id_seq OWNED BY segnalazioni.tipo_segnalanti.id;


--
-- Name: tipo_stato_incarichi; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.tipo_stato_incarichi (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: tipo_stato_provvedimenti_cautelari; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.tipo_stato_provvedimenti_cautelari (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: tipo_stato_sopralluoghi; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.tipo_stato_sopralluoghi (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: tipo_coc_interno; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.tipo_coc_interno (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: tipo_origine_provvedimenti_cautelari; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.tipo_origine_provvedimenti_cautelari (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: tipo_funzione_coc; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.tipo_funzione_coc (
    id integer NOT NULL,
    funzione character varying NOT NULL
);


--
-- Name: join_tipo_funzione_coc_id_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.join_tipo_funzione_coc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: join_tipo_funzione_coc_id_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.join_tipo_funzione_coc_id_seq OWNED BY users.tipo_funzione_coc.id;


--
-- Name: tipo_coc_interno_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.tipo_coc_interno_seq
    START WITH 13
    INCREMENT BY 1
    MINVALUE 13
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipo_coc_interno_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.tipo_coc_interno_seq OWNED BY users.tipo_coc_interno.id;


--
-- Name: tipo_mail_provvedimenti_cautelari; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.tipo_mail_provvedimenti_cautelari (
    id integer NOT NULL,
    mail character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: tipo_mail_provvedimenti_cautelari_id_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.tipo_mail_provvedimenti_cautelari_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipo_mail_provvedimenti_cautelari_id_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.tipo_mail_provvedimenti_cautelari_id_seq OWNED BY users.tipo_mail_provvedimenti_cautelari.id;


--
-- Name: tipo_origine_provvedimenti_cautelari_id_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.tipo_origine_provvedimenti_cautelari_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipo_origine_provvedimenti_cautelari_id_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.tipo_origine_provvedimenti_cautelari_id_seq OWNED BY users.tipo_origine_provvedimenti_cautelari.id;


--
-- Name: tipo_allerta id; Type: DEFAULT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.tipo_allerta ALTER COLUMN id SET DEFAULT nextval('eventi.tipo_allerta_id_seq'::regclass);


--
-- Name: tipo_evento id; Type: DEFAULT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.tipo_evento ALTER COLUMN id SET DEFAULT nextval('eventi.tipo_evento_id_seq'::regclass);


--
-- Name: tipo_foc id; Type: DEFAULT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.tipo_foc ALTER COLUMN id SET DEFAULT nextval('eventi.tipo_foc_id_seq'::regclass);


--
-- Name: tipo_idrometri_arpa id; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.tipo_idrometri_arpa ALTER COLUMN id SET DEFAULT nextval('geodb.tipo_idrometri_arpa_id_seq'::regclass);


--
-- Name: tipo_lettura_mire id; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.tipo_lettura_mire ALTER COLUMN id SET DEFAULT nextval('geodb.tipo_lettura_mire_id_seq'::regclass);


--
-- Name: tipo_criticita id; Type: DEFAULT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.tipo_criticita ALTER COLUMN id SET DEFAULT nextval('segnalazioni.tipo_criticita_id_seq'::regclass);


--
-- Name: tipo_oggetti_rischio id; Type: DEFAULT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.tipo_oggetti_rischio ALTER COLUMN id SET DEFAULT nextval('segnalazioni.tipo_oggetti_rischio_id_seq'::regclass);


--
-- Name: tipo_coc_interno id; Type: DEFAULT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.tipo_coc_interno ALTER COLUMN id SET DEFAULT nextval('users.tipo_coc_interno_seq'::regclass);


--
-- Name: tipo_funzione_coc id; Type: DEFAULT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.tipo_funzione_coc ALTER COLUMN id SET DEFAULT nextval('users.join_tipo_funzione_coc_id_seq'::regclass);


--
-- Name: tipo_mail_provvedimenti_cautelari id; Type: DEFAULT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.tipo_mail_provvedimenti_cautelari ALTER COLUMN id SET DEFAULT nextval('users.tipo_mail_provvedimenti_cautelari_id_seq'::regclass);


--
-- Name: tipo_origine_provvedimenti_cautelari id; Type: DEFAULT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.tipo_origine_provvedimenti_cautelari ALTER COLUMN id SET DEFAULT nextval('users.tipo_origine_provvedimenti_cautelari_id_seq'::regclass);


--
-- Data for Name: tipo_allerta; Type: TABLE DATA; Schema: eventi; Owner: -
--

COPY eventi.tipo_allerta (id, descrizione, valido, rgb_hex) FROM stdin;
1	Gialla	t	#ffd800\n
3	Rossa	t	#e00000
2	Arancione	t	#ff8c00
\.


--
-- Data for Name: tipo_evento; Type: TABLE DATA; Schema: eventi; Owner: -
--

COPY eventi.tipo_evento (id, descrizione, valido, notifiche) FROM stdin;
4	Sisma	t	t
1	Idrologico	t	t
3	Avviso meteo	t	t
5	Incendio	t	t
6	Incidente industriale	t	t
7	Geologico	t	t
2	Nivologico	t	t
9	Altro tipo di evento	t	t
8	Evento mensile	t	f
\.


--
-- Data for Name: tipo_foc; Type: TABLE DATA; Schema: eventi; Owner: -
--

COPY eventi.tipo_foc (id, descrizione, valido, rgb_hex) FROM stdin;
1	Attenzione	t	#009aff
2	Pre-allarme	t	#5945ff
3	Allarme	t	#950077
\.


--
-- Data for Name: tipo_idrometri_arpa; Type: TABLE DATA; Schema: geodb; Owner: -
--

COPY geodb.tipo_idrometri_arpa (id, alt, imgpath, increment, lat, lon, municipality, name, otherhtml, refdate, shortcode, stationid, updatedatetime, value, geom) FROM stdin;
1	20		0	44.4367099999999979	8.82023000000000046	Genova	Genova - Granara	0	2020-03-30 22:00:00+02	VAREN	1	03/30/2020 10:14:35 PM	-0.0700000000000000067	01010000A06F1E0000AF4627F3F4A41D41F6B258B512C552410000000000000000
17	28		0	44.4203199999999967	8.97019000000000055	Genova	Genova - Fereggiano	0	2020-03-30 22:00:00+02	GEFER	1	\N	0.380000000000000004	01010000A06F1E0000966585C96B5F1E417235DEC047C352410000000000000000
18	10		0	44.396250000000002	8.98468000000000089	Genova	Genova - Sturla	0	2020-03-30 22:00:00+02	GSTUR	1	\N	-0.110000000000000001	01010000A06F1E0000F1812ABF6F711E41E90B8546ABC052410000000000000000
21	62		0	44.4540000000000006	8.98635999999999946	Genova	Genova - Molassana	0	2020-03-30 22:00:00+02	GEMOL	1	\N	0.0400000000000000008	01010000A06F1E0000C1739E318B731E413F4A42EEEEC652410000000000000000
32	10		0	44.4109399999999965	8.95395000000000074	Genova	Genova - Firpo	0	2020-03-30 22:00:00+02	FIRPO	1	\N	0.380000000000000004	01010000A06F1E00009CD2A43D364B1E4196101D6E43C252410000000000000000
37	26		0	44.4378799999999998	8.8895199999999992	Genova	Genova - Rivarolo	0	2020-03-30 22:00:00+02	GERIV	1	\N	0.429999999999999993	01010000A06F1E0000FBC567121FFB1D41083BE5C030C552410000000000000000
38	75		0	44.4885200000000012	8.90010000000000012	Genova	Genova - Pontedecimo	0	2020-03-30 22:00:00+02	GEPTX	1	\N	0.450000000000000011	01010000A06F1E00002E00457762081E412BAD3CB7AECA52410000000000000000
40	150		0	44.4407599999999974	9.04716999999999949	Genova	La Presa	0	2020-03-30 22:00:00+02	LAPRS	1	\N	0.969999999999999973	01010000A06F1E0000ED2ACED026BF1E418D5DE6837FC552410000000000000000
52	35		0	44.4394599999999969	8.7460299999999993	Genova	Molinetto	0	2020-03-30 22:00:00+02	MOLIN	1	\N	0.569999999999999951	01010000A06F1E00000A86A24BB4481D41B3314FFC62C552410000000000000000
\.


--
-- Data for Name: tipo_idrometri_comune; Type: TABLE DATA; Schema: geodb; Owner: -
--

COPY geodb.tipo_idrometri_comune (id, nome, first_rec, last_rec, usato, lat, lon, geom, doppione_arpa) FROM stdin;
11	T. Fereggiano - Marassi	2013-06-20 15:45:00	2021-11-15 02:45:00	t	\N	\N	\N	f
13	Leiro ID5	2013-12-05 00:00:00	2017-03-28 07:00:00	f	\N	\N	\N	f
20	T. Polcevera - Pontedecimo	1970-01-01 01:30:00	2021-11-15 02:45:00	t	\N	\N	\N	t
24	La Presa - T. Bisagno	2015-12-02 12:00:00	2021-11-15 02:45:00	t	\N	\N	\N	f
27	T. Leiro - Molinetto	2015-12-02 12:00:00	2021-11-15 02:45:00	t	\N	\N	\N	f
28	T. Varenna - Granara	2015-12-02 13:15:00	2021-11-15 02:45:00	t	\N	\N	\N	f
29	T. Bisagno - Pass. Firpo	2015-12-02 13:30:00	2021-11-15 02:45:00	t	\N	\N	\N	f
31	T. Molinassi - Sestri P.	2017-03-08 10:30:00	2021-11-15 02:45:00	t	\N	\N	\N	f
32	T. Chiaravagna - Sestri P.	2017-03-07 14:30:00	2021-11-15 02:45:00	t	\N	\N	\N	f
33	T. Ruscarolo - Sestri P.	2017-03-07 12:30:00	2021-11-15 02:45:00	t	\N	\N	\N	f
34	T. Cantarena - Sestri P.	2017-03-08 14:00:00	2021-11-15 02:45:00	t	\N	\N	\N	f
35	T. Leiro - Voltri	2017-03-22 15:00:00	2021-11-15 02:45:00	t	\N	\N	\N	f
36	T. Polcevera - Rivarolo	2018-08-21 08:45:00	2021-11-15 02:45:00	t	\N	\N	\N	f
37	Staglieno - R. Veilino (Bisagno)	2019-03-21 09:00:00	2021-11-15 02:30:00	t	\N	\N	\N	f
40	T. Sturla - Sturla	2021-03-17 15:00:00	2021-11-15 02:45:00	t	\N	\N	\N	f
51	T. Varenna - San Carlo di Cese	2021-03-30 09:00:00	2021-11-15 02:45:00	t	\N	\N	\N	f
52	T. Torbella - Rivarolo	2021-04-21 12:00:00	2021-11-15 02:45:00	t	\N	\N	\N	f
53	T. San Pietro - Prà	2021-05-04 11:45:00	2021-11-15 02:45:00	t	\N	\N	\N	f
54	T. Cerusa - Fabbriche	2021-05-05 11:15:00	2021-11-15 02:45:00	t	\N	\N	\N	f
55	T. Verde - Pontedecimo	2021-05-06 12:15:00	2021-11-15 02:45:00	t	\N	\N	\N	f
56	T. Nervi - Nervi	2021-05-12 11:30:00	2021-11-11 16:30:00	f	\N	\N	\N	f
1	Chiaravagna ID1	2012-03-05 11:10:00	2017-03-07 12:30:00	f	\N	\N	\N	f
2	Ruscarolo ID2	2012-03-06 15:30:00	2017-03-07 08:00:00	f	\N	\N	\N	f
4	Cantarena ID3	2012-04-26 00:00:00	2017-03-08 11:50:00	f	\N	\N	\N	f
5	Molinassi ID4	2012-03-23 00:00:00	2017-03-08 06:50:00	f	\N	\N	\N	f
8	Geirato - T. Geirato (Bisagno)	2013-06-20 15:45:00	2021-11-15 02:45:00	t	\N	\N	\N	f
9	T. Bisagno - Molassana	2013-06-20 15:30:00	2021-11-15 02:45:00	t	\N	\N	\N	f
\.


--
-- Data for Name: tipo_lettura_mire; Type: TABLE DATA; Schema: geodb; Owner: -
--

COPY geodb.tipo_lettura_mire (id, descrizione, valido, rgb_hex) FROM stdin;
1	Verde	t	#00bb2d
2	Giallo	t	#ffff00
3	Rosso	t	#cb3234
\.


--
-- Data for Name: tipo_criticita; Type: TABLE DATA; Schema: segnalazioni; Owner: -
--

COPY segnalazioni.tipo_criticita (id, descrizione, valido) FROM stdin;
3	Frana	t
4	Neve	t
5	Ghiaccio	t
6	Caduta alberi	t
7	Caduta cose	t
8	Disagio per la salute	t
9	Incendio	t
10	Scoppio/esplosione	t
11	Inquinamento	t
12	Incidente nei trasporti	t
13	Crollo/lesione edifici	t
14	Crollo/lesione infrastrutture	t
1	Esondazione	t
2	Allagamento	t
\.


--
-- Data for Name: tipo_oggetti_rischio; Type: TABLE DATA; Schema: segnalazioni; Owner: -
--

COPY segnalazioni.tipo_oggetti_rischio (id, nome_tabella, descrizione, valido, campo_identificativo, elenco_elementi_segnalazione) FROM stdin;
1	geodb.civici	Civici	t	id	f
2	geodb.elemento_stradale	Tratto stradale	t	id_oggetto	t
3	geodb.aree_verdi	Aree verdi	t	cod_avd	t
7	geodb.rir_impianti	Impianti a rischio industriale	t	pk_id	t
10	geodb.sottopassi	Sottopassi	t	id_crit	t
12	geodb.fiumi	Rii e torrenti	t	id	t
4	geodb.edifici	Edifici	t	id_oggetto	t
\.


--
-- Data for Name: tipo_provvedimenti_cautelari; Type: TABLE DATA; Schema: segnalazioni; Owner: -
--

COPY segnalazioni.tipo_provvedimenti_cautelari (id, descrizione, valido) FROM stdin;
1	Sgombero civico	t
2	Interdizione all'accesso	t
3	Chiusura tratto stradale	t
\.


--
-- Data for Name: tipo_segnalanti; Type: TABLE DATA; Schema: segnalazioni; Owner: -
--

COPY segnalazioni.tipo_segnalanti (id, descrizione, valido) FROM stdin;
1	Presidio territoriale ( Volontariato e PM)	t
2	Cittadino e/o associazione di cittadini	t
3	Attività economiche (negozi, imprese, uffici etc.)	t
4	Strutture comunali (Direzioni e Settori dell'Ente)	t
5	Strutture operative (VVF, Forze dell'Ordine, Primo Soccorso, etc.)	t
6	Enti (Prefettura, Regione, Città Metropolitana, Dipartimento PC, ASL, etc.)	t
999	Altro	t
\.


--
-- Data for Name: tipo_stato_incarichi; Type: TABLE DATA; Schema: segnalazioni; Owner: -
--

COPY segnalazioni.tipo_stato_incarichi (id, descrizione, valido) FROM stdin;
1	Inviato ma non ancora preso in carico\n	t
2	Preso in carico	t
3	Chiuso\n	t
4	Rifiutato	t
\.


--
-- Data for Name: tipo_stato_provvedimenti_cautelari; Type: TABLE DATA; Schema: segnalazioni; Owner: -
--

COPY segnalazioni.tipo_stato_provvedimenti_cautelari (id, descrizione, valido) FROM stdin;
2	Preso in carico	t
3	Provvedimento completato	t
1	Inviato ma non ancora preso in carico	t
\.


--
-- Data for Name: tipo_stato_sopralluoghi; Type: TABLE DATA; Schema: segnalazioni; Owner: -
--

COPY segnalazioni.tipo_stato_sopralluoghi (id, descrizione, valido) FROM stdin;
1	Inviato ma non ancora preso in carico\n	t
2	Preso in carico	t
3	Chiuso\n	t
\.


--
-- Data for Name: tipo_coc_interno; Type: TABLE DATA; Schema: users; Owner: -
--

COPY users.tipo_coc_interno (id, descrizione, valido) FROM stdin;
2	Direzione Sistemi Informativi	t
4	Direzione opere idrauliche e sanitarie	t
5	Direzione ambiente e igiene	t
6	Direzione Patrimonio, Demanio e Impiantistica sportiva	t
7	Direzione Scuola e Politiche Giovanili	t
8	Direzione Politiche Sociali	t
9	Direzione Sviluppo Economico	t
11	Direzione Mobilità	t
12	Direzione Manutenzione e Sviluppo Municipi	t
3	Direzione gabinetto del sindaco (attività di comunicazione)	t
10	Direzione Protezione Civile	t
1	Direzione Facility Management	t
\.


--
-- Data for Name: tipo_funzione_coc; Type: TABLE DATA; Schema: users; Owner: -
--

COPY users.tipo_funzione_coc (id, funzione) FROM stdin;
1	Funzione -  Responsabile
2	Autorità di Protezione Civile -  Sindaco
3	Autorità di Protezione Civile - Consigliere Delegato Protezione Civile
4	Coordinamento del COC - Direttore Generale
5	Coordinamento del COC  - sostituto  Direttore Generale
6	Attività Economica, Turistica e Culturale Telecomunicazioni - Direttore Sviluppo Economico
7	Attività Economica, Turistica e Culturale Telecomunicazioni -  sostituto Direttore Sviluppo Economico
8	Servizi Essenziali, Materiali e Mezzi, Censimento Danni a Persone e Cose - Direttore Coordinamento Risorse Tecnico Operative
9	Servizi Essenziali, Materiali e Mezzi, Censimento Danni a Persone e Cose – sostituto Direttore Coordinamento Risorse Tecnico Operative
10	Assistenza Sociale, Servizi Civici, Attività Scolastica -  Direttore Coordinamento Servizi alla Comunità
11	Assistenza Sociale, Servizi Civici, Attività Scolastica - sostituto Direttore Coordinamento Servizi alla Comunità
12	Coordinamento Aree, Mobilità -  Direttore Sviluppo del Territorio
13	Coordinamento Aree, Mobilità -  sostituto Direttore Sviluppo del Territorio
14	Strutture Operative Locali e Viabilità - Comandante Polizia Locale
15	Strutture Operative Locali e Viabilità – sostituto Comandante Polizia Locale
16	Tecnica e Pianificazione, Volontariato, Assistenza alla Popolazione, Assistenza Sanitaria – Dirigente Protezione Civile
17	Tecnica e Pianificazione, Volontariato, Assistenza alla Popolazione, Assistenza Sanitaria – sostituto Dirigente Protezione Civile
18	Municipi -  Direttore Governo e Sicurezza del Territori Municipali
19	Municipi -  sostituto Direttore Governo e Sicurezza del Territori Municipali
20	Comunicazione alla Popolazione -  Coordinatore Area Gabinetto del Sindaco
21	Comunicazione alla Popolazione -  sostituto Coordinatore Area Gabinetto del Sindaco
22	Amministrativa -  Direttore Area Servizi Centrali Amministrativi
23	Amministrativa -  sostituto Direttore Area Servizi Centrali Amministrativi
\.


--
-- Data for Name: tipo_mail_provvedimenti_cautelari; Type: TABLE DATA; Schema: users; Owner: -
--

COPY users.tipo_mail_provvedimenti_cautelari (id, mail, valido) FROM stdin;
1	roberto.marzocchi@gter.it	t
\.


--
-- Data for Name: tipo_origine_provvedimenti_cautelari; Type: TABLE DATA; Schema: users; Owner: -
--

COPY users.tipo_origine_provvedimenti_cautelari (id, descrizione, valido) FROM stdin;
1	Pubblica incolumità	t
\.


--
-- Name: tipo_allerta_id_seq; Type: SEQUENCE SET; Schema: eventi; Owner: -
--

SELECT pg_catalog.setval('eventi.tipo_allerta_id_seq', 3, true);


--
-- Name: tipo_evento_id_seq; Type: SEQUENCE SET; Schema: eventi; Owner: -
--

SELECT pg_catalog.setval('eventi.tipo_evento_id_seq', 9, true);


--
-- Name: tipo_foc_id_seq; Type: SEQUENCE SET; Schema: eventi; Owner: -
--

SELECT pg_catalog.setval('eventi.tipo_foc_id_seq', 3, true);


--
-- Name: tipo_idrometri_arpa_id_seq; Type: SEQUENCE SET; Schema: geodb; Owner: -
--

SELECT pg_catalog.setval('geodb.tipo_idrometri_arpa_id_seq', 54, true);


--
-- Name: tipo_lettura_mire_id_seq; Type: SEQUENCE SET; Schema: geodb; Owner: -
--

SELECT pg_catalog.setval('geodb.tipo_lettura_mire_id_seq', 3, true);


--
-- Name: tipo_criticita_id_seq; Type: SEQUENCE SET; Schema: segnalazioni; Owner: -
--

SELECT pg_catalog.setval('segnalazioni.tipo_criticita_id_seq', 14, true);


--
-- Name: tipo_oggetti_rischio_id_seq; Type: SEQUENCE SET; Schema: segnalazioni; Owner: -
--

SELECT pg_catalog.setval('segnalazioni.tipo_oggetti_rischio_id_seq', 12, true);


--
-- Name: tipo_segnalanti_id_seq; Type: SEQUENCE SET; Schema: segnalazioni; Owner: -
--

SELECT pg_catalog.setval('segnalazioni.tipo_segnalanti_id_seq', 7, true);


--
-- Name: join_tipo_funzione_coc_id_seq; Type: SEQUENCE SET; Schema: users; Owner: -
--

SELECT pg_catalog.setval('users.join_tipo_funzione_coc_id_seq', 24, true);


--
-- Name: tipo_coc_interno_seq; Type: SEQUENCE SET; Schema: users; Owner: -
--

SELECT pg_catalog.setval('users.tipo_coc_interno_seq', 13, true);


--
-- Name: tipo_mail_provvedimenti_cautelari_id_seq; Type: SEQUENCE SET; Schema: users; Owner: -
--

SELECT pg_catalog.setval('users.tipo_mail_provvedimenti_cautelari_id_seq', 1, true);


--
-- Name: tipo_origine_provvedimenti_cautelari_id_seq; Type: SEQUENCE SET; Schema: users; Owner: -
--

SELECT pg_catalog.setval('users.tipo_origine_provvedimenti_cautelari_id_seq', 1, true);


--
-- Name: tipo_allerta tipo_allerta_pkey; Type: CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.tipo_allerta
    ADD CONSTRAINT tipo_allerta_pkey PRIMARY KEY (id);


--
-- Name: tipo_evento tipo_evento_pkey; Type: CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.tipo_evento
    ADD CONSTRAINT tipo_evento_pkey PRIMARY KEY (id);


--
-- Name: tipo_foc tipo_foc_pkey; Type: CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.tipo_foc
    ADD CONSTRAINT tipo_foc_pkey PRIMARY KEY (id);


--
-- Name: tipo_idrometri_arpa tipo_idrometri_arpa_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.tipo_idrometri_arpa
    ADD CONSTRAINT tipo_idrometri_arpa_pkey PRIMARY KEY (id);


--
-- Name: tipo_idrometri_comune tipo_idrometri_comune_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.tipo_idrometri_comune
    ADD CONSTRAINT tipo_idrometri_comune_pkey PRIMARY KEY (id);


--
-- Name: tipo_lettura_mire tipo_lettura_mire_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.tipo_lettura_mire
    ADD CONSTRAINT tipo_lettura_mire_pkey PRIMARY KEY (id);


--
-- Name: tipo_provvedimenti_cautelari pkey_t_tipo_provvedimenti_cautelari; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.tipo_provvedimenti_cautelari
    ADD CONSTRAINT pkey_t_tipo_provvedimenti_cautelari PRIMARY KEY (id);


--
-- Name: tipo_stato_provvedimenti_cautelari pkey_t_tipo_stato_provvedimenti_cautelari; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.tipo_stato_provvedimenti_cautelari
    ADD CONSTRAINT pkey_t_tipo_stato_provvedimenti_cautelari PRIMARY KEY (id);


--
-- Name: tipo_stato_sopralluoghi pky_t_tipo_stato_sopralluoghi; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.tipo_stato_sopralluoghi
    ADD CONSTRAINT pky_t_tipo_stato_sopralluoghi PRIMARY KEY (id);


--
-- Name: tipo_stato_incarichi t_tipo_stato_incarichi; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.tipo_stato_incarichi
    ADD CONSTRAINT t_tipo_stato_incarichi PRIMARY KEY (id);


--
-- Name: tipo_criticita tipo_criticita_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.tipo_criticita
    ADD CONSTRAINT tipo_criticita_pkey PRIMARY KEY (id);


--
-- Name: tipo_oggetti_rischio tipo_oggetti_rischio_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.tipo_oggetti_rischio
    ADD CONSTRAINT tipo_oggetti_rischio_pkey PRIMARY KEY (id);


--
-- Name: tipo_segnalanti tipo_segnalanti_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.tipo_segnalanti
    ADD CONSTRAINT tipo_segnalanti_pkey PRIMARY KEY (id);


--
-- Name: tipo_funzione_coc join_tipo_funzione_coc_pk; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.tipo_funzione_coc
    ADD CONSTRAINT join_tipo_funzione_coc_pk PRIMARY KEY (id);


--
-- Name: tipo_mail_provvedimenti_cautelari pkey_t_tipo_mail_provvedimenti_cautelari; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.tipo_mail_provvedimenti_cautelari
    ADD CONSTRAINT pkey_t_tipo_mail_provvedimenti_cautelari PRIMARY KEY (id);


--
-- Name: tipo_origine_provvedimenti_cautelari pkey_t_tipo_origine_provvedimenti_cautelari; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.tipo_origine_provvedimenti_cautelari
    ADD CONSTRAINT pkey_t_tipo_origine_provvedimenti_cautelari PRIMARY KEY (id);


--
-- Name: tipo_coc_interno pkey_tipo_coc_interno; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.tipo_coc_interno
    ADD CONSTRAINT pkey_tipo_coc_interno PRIMARY KEY (id);


--
-- Name: tipo_idrometri_arpa_geom_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX tipo_idrometri_arpa_geom_geom_idx ON geodb.tipo_idrometri_arpa USING gist (geom);


--
-- PostgreSQL database dump complete
--

