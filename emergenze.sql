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

--
-- Name: eventi; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA eventi;


--
-- Name: geodb; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA geodb;


--
-- Name: report; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA report;


--
-- Name: segnalazioni; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA segnalazioni;


--
-- Name: users; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA users;


--
-- Name: varie; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA varie;


--
-- Name: pg_stat_statements; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_stat_statements WITH SCHEMA public;


--
-- Name: EXTENSION pg_stat_statements; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pg_stat_statements IS 'track execution statistics of all SQL statements executed';


--
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry, geography, and raster spatial types and functions';


--
-- Name: roundtoquarterhour(timestamp with time zone); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.roundtoquarterhour(sub_data timestamp with time zone) RETURNS timestamp without time zone
    LANGUAGE plpgsql
    AS $$ 
 declare out_data timestamp;
 begin
 
out_data =  to_timestamp((to_char(sub_data, 'YYYY/MM/DD HH24:')||
            (extract (minute from sub_data) - extract (minute from sub_data)::numeric%15::numeric)::varchar), 'YYYY-MM-DD HH24:MI:SS');

 RETURN out_data; 
 END;
 $$;


--
-- Name: roundtoquarterhouridro(timestamp with time zone); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.roundtoquarterhouridro(sub_data timestamp with time zone) RETURNS timestamp without time zone
    LANGUAGE plpgsql
    AS $$ 
 declare out_data timestamp;
 declare minuti numeric;
 begin
 
 --minuti = extract (minute from sub_data) - extract (minute from sub_data)::numeric%15::numeric; --provare con time_part al posto di extract
--minuti = date_part ('minute', sub_data) -date_part ('minute', sub_data)::numeric%15::numeric; --provare con time_part al posto di extract
 minuti = (date_part ('minute', now()+ interval '13 minute')::integer/15)*15; --provare con time_part al posto di extract
--out_data =  date_trunc ('hour',sub_data) + interval concat((extract (minute from sub_data) - extract (minute from sub_data)::numeric%15::numeric)::varchar, ' minutes');
out_data =  date_trunc ('hour',sub_data) + interval '1 minute' * minuti;
 
 RETURN out_data; 
 END;
 $$;


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: join_municipi; Type: TABLE; Schema: eventi; Owner: -
--

CREATE TABLE eventi.join_municipi (
    id_evento integer NOT NULL,
    id_municipio character varying NOT NULL,
    data_ora_inizio timestamp without time zone NOT NULL,
    data_ora_fine timestamp without time zone
);


--
-- Name: join_tipo_allerta; Type: TABLE; Schema: eventi; Owner: -
--

CREATE TABLE eventi.join_tipo_allerta (
    id_evento integer NOT NULL,
    id_tipo_allerta integer NOT NULL,
    messaggio_rlg character varying NOT NULL,
    data_ora_inizio_allerta timestamp without time zone NOT NULL,
    data_ora_fine_allerta timestamp without time zone,
    valido boolean DEFAULT true NOT NULL,
    allegato character varying
);


--
-- Name: join_tipo_evento; Type: TABLE; Schema: eventi; Owner: -
--

CREATE TABLE eventi.join_tipo_evento (
    id_evento integer NOT NULL,
    id_tipo_evento integer NOT NULL
);


--
-- Name: join_tipo_foc; Type: TABLE; Schema: eventi; Owner: -
--

CREATE TABLE eventi.join_tipo_foc (
    id_evento integer NOT NULL,
    id_tipo_foc integer NOT NULL,
    data_ora_inizio_foc timestamp without time zone NOT NULL,
    data_ora_fine_foc timestamp without time zone
);


--
-- Name: t_attivazione_nverde; Type: TABLE; Schema: eventi; Owner: -
--

CREATE TABLE eventi.t_attivazione_nverde (
    id_evento integer NOT NULL,
    data_ora_inizio timestamp without time zone NOT NULL,
    data_ora_fine timestamp without time zone
);


--
-- Name: t_bollettini; Type: TABLE; Schema: eventi; Owner: -
--

CREATE TABLE eventi.t_bollettini (
    tipo character varying NOT NULL,
    nomefile character varying NOT NULL,
    data_ora_emissione timestamp without time zone,
    data_download timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: t_eventi; Type: TABLE; Schema: eventi; Owner: -
--

CREATE TABLE eventi.t_eventi (
    id integer NOT NULL,
    data_ora_inizio_evento timestamp without time zone DEFAULT now() NOT NULL,
    data_ora_fine_evento timestamp without time zone,
    valido boolean DEFAULT true,
    fine_sospensione timestamp without time zone,
    data_ora_chiusura timestamp without time zone
);


--
-- Name: t_eventi_id_seq; Type: SEQUENCE; Schema: eventi; Owner: -
--

CREATE SEQUENCE eventi.t_eventi_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_eventi_id_seq; Type: SEQUENCE OWNED BY; Schema: eventi; Owner: -
--

ALTER SEQUENCE eventi.t_eventi_id_seq OWNED BY eventi.t_eventi.id;


--
-- Name: t_note_eventi; Type: TABLE; Schema: eventi; Owner: -
--

CREATE TABLE eventi.t_note_eventi (
    id integer NOT NULL,
    id_evento integer,
    nota character varying,
    data_ora_nota timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: t_note_eventi_id_seq; Type: SEQUENCE; Schema: eventi; Owner: -
--

CREATE SEQUENCE eventi.t_note_eventi_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_note_eventi_id_seq; Type: SEQUENCE OWNED BY; Schema: eventi; Owner: -
--

ALTER SEQUENCE eventi.t_note_eventi_id_seq OWNED BY eventi.t_note_eventi.id;


--
-- Name: v_allerte; Type: VIEW; Schema: eventi; Owner: -
--

CREATE VIEW eventi.v_allerte AS
 SELECT a.id_evento,
    a.id_tipo_allerta,
    a.messaggio_rlg,
    c.descrizione,
    c.rgb_hex,
    a.data_ora_inizio_allerta,
    a.data_ora_fine_allerta,
    a.valido,
    a.allegato
   FROM eventi.join_tipo_allerta a,
    eventi.t_eventi b,
    eventi.tipo_allerta c
  WHERE ((a.id_evento = b.id) AND (c.id = a.id_tipo_allerta))
  ORDER BY a.data_ora_inizio_allerta;


--
-- Name: v_bollettini; Type: VIEW; Schema: eventi; Owner: -
--

CREATE VIEW eventi.v_bollettini AS
 SELECT
        CASE
            WHEN ((t_bollettini.tipo)::text = 'Met_A'::text) THEN 'Vigilanza Meteo'::text
            WHEN ((t_bollettini.tipo)::text = 'Idr_A'::text) THEN 'Idrogeologico'::text
            WHEN ((t_bollettini.tipo)::text = 'Niv_A'::text) THEN 'Nivologico'::text
            WHEN ((t_bollettini.tipo)::text = 'PC'::text) THEN 'Bollettino allerte'::text
            ELSE NULL::text
        END AS tipo,
        CASE
            WHEN ((t_bollettini.tipo)::text = 'Met_A'::text) THEN 'ARPA'::text
            WHEN ((t_bollettini.tipo)::text = 'Idr_A'::text) THEN 'ARPA'::text
            WHEN ((t_bollettini.tipo)::text = 'Niv_A'::text) THEN 'ARPA'::text
            WHEN ((t_bollettini.tipo)::text = 'PC'::text) THEN 'PC Liguria'::text
            ELSE NULL::text
        END AS ente,
        CASE
            WHEN ((t_bollettini.tipo)::text = 'Met_A'::text) THEN 'thermometer-quarter'::text
            WHEN ((t_bollettini.tipo)::text = 'Idr_A'::text) THEN 'bolt'::text
            WHEN ((t_bollettini.tipo)::text = 'Niv_A'::text) THEN 'snowflake'::text
            WHEN ((t_bollettini.tipo)::text = 'PC'::text) THEN 'exclamation-triangle'::text
            ELSE NULL::text
        END AS icon,
    (t_bollettini.nomefile)::text AS nomepdf,
    (((t_bollettini.tipo)::text || '/'::text) || (t_bollettini.nomefile)::text) AS nomefile,
    to_char(t_bollettini.data_ora_emissione, 'YYYY/MM/DD HH24:MI'::text) AS data_ora_emissione,
    to_char(t_bollettini.data_download, 'YYYY/MM/DD HH24:MI'::text) AS data_download
   FROM eventi.t_bollettini
  ORDER BY t_bollettini.data_download DESC;


--
-- Name: v_eventi; Type: VIEW; Schema: eventi; Owner: -
--

CREATE VIEW eventi.v_eventi AS
 SELECT e.id,
    d.descrizione,
    e.data_ora_inizio_evento,
    e.data_ora_fine_evento,
    e.valido
   FROM ((eventi.t_eventi e
     JOIN eventi.join_tipo_evento te ON ((te.id_evento = e.id)))
     JOIN eventi.tipo_evento d ON ((te.id_tipo_evento = d.id)));


--
-- Name: v_foc; Type: VIEW; Schema: eventi; Owner: -
--

CREATE VIEW eventi.v_foc AS
 SELECT a.id_evento,
    a.id_tipo_foc,
    c.descrizione,
    c.rgb_hex,
    a.data_ora_inizio_foc,
    a.data_ora_fine_foc
   FROM eventi.join_tipo_foc a,
    eventi.t_eventi b,
    eventi.tipo_foc c
  WHERE ((a.id_evento = b.id) AND (c.id = a.id_tipo_foc))
  ORDER BY a.data_ora_inizio_foc;


--
-- Name: anagrafe; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.anagrafe (
    gid integer NOT NULL,
    matricola numeric(9,0) NOT NULL,
    famiglia numeric(9,0),
    sesso character varying(1),
    datanas character varying(8),
    istat character varying(8),
    comune character varying(100),
    prov character varying(3),
    sigpro character varying(2),
    nazione character varying(50),
    sigla character varying(3),
    cee character varying(1),
    statociv character varying(2),
    naz character varying(3),
    cittad character varying(50),
    parentela character varying(3),
    codvia numeric,
    via character varying(81),
    numciv numeric,
    letciv character varying(10),
    colciv character varying(1),
    numint character varying(5),
    letint character varying(5),
    scala character varying(5),
    numcap character varying(20),
    cambioind character varying(8),
    sezele numeric(5,0),
    sezicen numeric(9,0),
    unurb numeric(9,0),
    circos numeric(5,0),
    municipio numeric(9,0),
    codint numeric,
    codfisc character varying(16),
    uso character varying(10),
    cognome character varying(60) NOT NULL,
    nome character varying(60)
);


--
-- Name: civici; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.civici (
    gid integer NOT NULL,
    id bigint NOT NULL,
    id_asta character varying(10),
    coord_nord double precision,
    coord_est double precision,
    codvia character varying(5),
    desvia character varying(150),
    numero character varying(4),
    lettera character varying(1),
    colore character varying(1),
    cap character varying(5),
    uso character varying(1) NOT NULL,
    tipo_accesso numeric(2,0),
    sottotipo_accesso numeric(2,0),
    provenienza numeric(2,0),
    angolo double precision,
    scala numeric(2,0),
    codice_indirizzo double precision,
    codice_controllo character varying(50),
    testo character varying(25),
    sezionecensimento2011 numeric(4,0),
    sezioneelettorale numeric(3,0),
    codmunicipio character varying(255),
    desmunicipio character varying(255),
    codcircoscrizione character varying(255),
    descircoscrizione character varying(255),
    codunitaurbanistica character varying(255),
    desunitaurbanistica character varying(255),
    tipooggettoriferimento character varying(30),
    idoggettoriferimento bigint,
    datainizio character varying(25),
    datafine character varying(25),
    datains date,
    machine_last_upd character varying(50),
    geom public.geometry(Point,3003)
);


--
-- Name: anagrafe_civici; Type: VIEW; Schema: geodb; Owner: -
--

CREATE VIEW geodb.anagrafe_civici AS
 SELECT a.codfisc,
    a.nome,
    a.cognome,
    a.gid,
    a.matricola,
    a.famiglia,
    a.datanas,
    a.sesso,
    a.nazione,
    a.codvia,
    a.via,
    a.numciv,
    a.letciv,
    a.colciv,
    a.numint,
    a.letint,
    a.scala,
    a.numcap,
    a.sezele,
    a.sezicen,
    a.unurb,
    a.circos,
    a.municipio,
    a.codint,
    a.uso,
    c.id AS id_civico
   FROM (geodb.anagrafe a
     JOIN geodb.civici c ON (((lpad((a.codvia)::text, 5, '0'::text) = (c.codvia)::text) AND (((COALESCE(c.numero, '0'::character varying))::integer)::numeric = a.numciv) AND (((c.lettera)::text = (a.letciv)::text) OR ((c.lettera IS NULL) AND (a.letciv IS NULL))) AND (((c.colore)::text = (a.colciv)::text) OR (((c.colore)::text IS NULL) AND ((a.colciv)::text = ' '::text))))))
  ORDER BY a.cognome;


--
-- Name: edifici; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.edifici (
    gid integer NOT NULL,
    tipo_oggetto character varying(25),
    id_oggetto bigint,
    superficie double precision,
    att_geom character varying(50),
    tipo character varying(255),
    geom public.geometry(MultiPolygonZ,3003)
);


--
-- Name: anagrafe_edifici; Type: VIEW; Schema: geodb; Owner: -
--

CREATE VIEW geodb.anagrafe_edifici AS
 SELECT a.codfisc,
    a.nome,
    a.cognome,
    a.gid,
    a.matricola,
    a.famiglia,
    a.datanas,
    a.sesso,
    a.nazione,
    a.codvia,
    a.via,
    a.numciv,
    a.letciv,
    a.colciv,
    a.numint,
    a.letint,
    a.scala,
    a.numcap,
    a.sezele,
    a.sezicen,
    a.unurb,
    a.circos,
    a.municipio,
    a.codint,
    a.uso,
    c.id AS id_civico,
    e.id_oggetto AS id_edificio
   FROM ((geodb.anagrafe a
     JOIN geodb.civici c ON (((lpad((a.codvia)::text, 5, '0'::text) = (c.codvia)::text) AND (((COALESCE(c.numero, '0'::character varying))::integer)::numeric = a.numciv) AND (((c.lettera)::text = (a.letciv)::text) OR ((c.lettera IS NULL) AND (a.letciv IS NULL))) AND (((c.colore)::text = (a.colciv)::text) OR (((c.colore)::text IS NULL) AND ((a.colciv)::text = ' '::text))))))
     JOIN geodb.edifici e ON (((c.idoggettoriferimento = e.id_oggetto) AND ((c.tipooggettoriferimento)::text = (e.tipo_oggetto)::text))))
  ORDER BY a.cognome;


--
-- Name: anagrafe_gid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.anagrafe_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: anagrafe_gid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.anagrafe_gid_seq OWNED BY geodb.anagrafe.gid;


--
-- Name: aree_verdi; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.aree_verdi (
    gid integer NOT NULL,
    fid numeric(10,0) NOT NULL,
    area numeric(20,8),
    cod_avd character varying(5),
    nome_ufficiale character varying(255),
    tipo_area character varying(255),
    superficie_lorda_appoggio numeric(20,8),
    superficie_netta_app numeric(20,8),
    manutenzione character varying(255),
    circoscrizione character varying(2),
    presa_in_carico character varying(255),
    note character varying(255),
    centroide character varying(50),
    cod_via character varying(5),
    cod_via_aster character varying(5),
    fid_aster numeric(10,0),
    geom public.geometry(MultiPolygonZ,3003)
);


--
-- Name: aree_verdi_gid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.aree_verdi_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: aree_verdi_gid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.aree_verdi_gid_seq OWNED BY geodb.aree_verdi.gid;


--
-- Name: civici_gid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.civici_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: civici_gid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.civici_gid_seq OWNED BY geodb.civici.gid;


--
-- Name: civici_wfs; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.civici_wfs (
    ogc_fid integer NOT NULL,
    wkb_geometry public.geometry(Geometry,3003),
    gml_id character varying,
    id double precision,
    codvia character varying,
    numero character varying,
    lettera character varying,
    colore character varying,
    testo character varying,
    angolo double precision,
    data_da character varying,
    data_a character varying,
    codice_controllo character varying,
    machine_last_upd character varying,
    id_oggetto_riferimento double precision,
    id_asta character varying,
    codice_indirizzo double precision,
    tipo_accesso double precision,
    provenienza double precision,
    scala double precision,
    tipo_oggetto_riferimento character varying,
    uso character varying,
    desvia character varying,
    sezione_censimento_2011 double precision,
    sezione_elettorale double precision,
    data_ins character varying,
    sottotipo_accesso double precision
);


--
-- Name: civici_ogc_fid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.civici_ogc_fid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: civici_ogc_fid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.civici_ogc_fid_seq OWNED BY geodb.civici_wfs.ogc_fid;


--
-- Name: edifici_gid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.edifici_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: edifici_gid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.edifici_gid_seq OWNED BY geodb.edifici.gid;


--
-- Name: elemento_stradale; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.elemento_stradale (
    gid integer NOT NULL,
    id_oggetto double precision NOT NULL,
    codvia character varying(5),
    desvia character varying(150),
    cod_tratta character varying(50),
    gestore character varying(50),
    classe_amministrativa character varying(255),
    classe_funzionale character varying(255),
    direzione character varying(255),
    stato character varying(255),
    tipo character varying(255),
    larghezza character varying(255),
    livello character varying(255),
    num_corsie bigint,
    fondo character varying(255),
    sede character varying(255),
    giunz_iniziale double precision,
    giunz_finale double precision,
    fittizia character varying(2),
    percorso character varying(20),
    prog_ini double precision,
    prog_fin double precision,
    reverse character varying(1),
    data_da date,
    data_a date,
    data_ins date,
    data_eli date,
    provenienza character varying(50),
    attend_geometrica character varying(50),
    geom public.geometry(LineStringZ,3003)
);


--
-- Name: elemento_stradale_gid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.elemento_stradale_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: elemento_stradale_gid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.elemento_stradale_gid_seq OWNED BY geodb.elemento_stradale.gid;


--
-- Name: fiumi; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.fiumi (
    gid integer NOT NULL,
    nome_rivo character varying(50),
    nome_rivo_regione character varying(50),
    nome_rivo_catasto character varying(50),
    nome_toponomastica character varying(50),
    tipo_rivo character varying(50),
    cod_rivo character varying(50),
    note character varying(255),
    id double precision,
    geom public.geometry(MultiPolygonZ,3003)
);


--
-- Name: fiumi_gid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.fiumi_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: fiumi_gid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.fiumi_gid_seq OWNED BY geodb.fiumi.gid;


--
-- Name: lettura_idrometri_arpa; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.lettura_idrometri_arpa (
    id_station character varying NOT NULL,
    data_ora timestamp without time zone NOT NULL,
    data_ora_reg timestamp without time zone DEFAULT now() NOT NULL,
    lettura double precision NOT NULL
);


--
-- Name: lettura_idrometri_comune; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.lettura_idrometri_comune (
    id_station character varying NOT NULL,
    data_ora timestamp without time zone NOT NULL,
    data_ora_reg timestamp without time zone DEFAULT now() NOT NULL,
    lettura double precision NOT NULL
);


--
-- Name: lettura_mire; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.lettura_mire (
    num_id_mira integer NOT NULL,
    data_ora timestamp without time zone NOT NULL,
    data_ora_reg timestamp without time zone DEFAULT now() NOT NULL,
    id_lettura integer NOT NULL
);


--
-- Name: lettura_mire_modifiche; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.lettura_mire_modifiche (
    num_id_mira integer NOT NULL,
    data_ora timestamp without time zone NOT NULL,
    data_ora_mod timestamp without time zone DEFAULT now() NOT NULL,
    old_id_lettura integer NOT NULL
);


--
-- Name: m_tables; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.m_tables (
    id integer NOT NULL,
    nome_oracle character varying,
    nome_emergenze character varying NOT NULL,
    "GEOM_NAME" character varying,
    "GEOM_TYPE" character varying,
    schema_emergenze character varying DEFAULT 'geodb'::character varying,
    descr character varying,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: m_tables_id_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.m_tables_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: m_tables_id_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.m_tables_id_seq OWNED BY geodb.m_tables.id;


--
-- Name: m_vie_unite; Type: VIEW; Schema: geodb; Owner: -
--

CREATE VIEW geodb.m_vie_unite AS
 SELECT s.codvia,
    s.desvia
   FROM geodb.elemento_stradale s
  GROUP BY s.codvia, s.desvia;


--
-- Name: municipi; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.municipi (
    id character varying NOT NULL,
    geom public.geometry(MultiPolygon,3003),
    codice_mun character varying(254),
    nome_munic character varying(254)
);


--
-- Name: presidi; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.presidi (
    gid integer NOT NULL,
    id_asta numeric(14,2),
    codvia character varying(1024),
    desvia character varying(1024),
    stato_al3 character varying(1024),
    stato_al4 character varying(1024),
    stato_al2 character varying(1024),
    lunghezza numeric(38,2),
    percorso character varying(10),
    geom public.geometry(LineStringZ,3003) NOT NULL
);


--
-- Name: presidi_gid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.presidi_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: presidi_gid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.presidi_gid_seq OWNED BY geodb.presidi.gid;


--
-- Name: punti_monitoraggio; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.punti_monitoraggio (
    gid integer NOT NULL,
    nome character varying(50),
    tipo character varying(50),
    note character varying(250),
    perc_al2 character varying(10),
    perc_al3 character varying(10),
    perc_al4 character varying(10),
    sot_ped_al character varying(10),
    sot_vei_al character varying(10),
    allarm_s_n character varying(10),
    num_id numeric(8,0),
    data_aggiornamento character varying(250),
    geom public.geometry(Point,3003) NOT NULL
);


--
-- Name: punti_monitoraggio_gid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.punti_monitoraggio_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: punti_monitoraggio_gid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.punti_monitoraggio_gid_seq OWNED BY geodb.punti_monitoraggio.gid;


--
-- Name: punti_monitoraggio_ok; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.punti_monitoraggio_ok (
    gid integer NOT NULL,
    id numeric(8,0),
    nome character varying(1024),
    tipo character varying(50),
    note character varying(3091),
    perc_al_g character varying(50),
    perc_al_a character varying(50),
    perc_al_r character varying(50),
    bacino character varying(50),
    geom public.geometry(Point,3003)
);


--
-- Name: punti_monitoraggio_ok_old; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.punti_monitoraggio_ok_old (
    gid integer NOT NULL,
    id numeric(8,0),
    nome character varying(1024),
    tipo character varying(50),
    note character varying(3091),
    geom public.geometry(Point,3003)
);


--
-- Name: punti_monitoraggio_ok_gid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.punti_monitoraggio_ok_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: punti_monitoraggio_ok_gid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.punti_monitoraggio_ok_gid_seq OWNED BY geodb.punti_monitoraggio_ok_old.gid;


--
-- Name: punti_monitoraggio_ok_gid_seq1; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.punti_monitoraggio_ok_gid_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: punti_monitoraggio_ok_gid_seq1; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.punti_monitoraggio_ok_gid_seq1 OWNED BY geodb.punti_monitoraggio_ok.gid;


--
-- Name: rir_impianti; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.rir_impianti (
    gid integer NOT NULL,
    pk_id bigint NOT NULL,
    nome_imp character varying(255),
    tipologia character varying(255),
    geom public.geometry(MultiPolygonZ,3003)
);


--
-- Name: rir_impianti_gid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.rir_impianti_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: rir_impianti_gid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.rir_impianti_gid_seq OWNED BY geodb.rir_impianti.gid;


--
-- Name: soglie_idrometri_arpa; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.soglie_idrometri_arpa (
    cod character varying NOT NULL,
    liv_arancione real,
    liv_rosso real
);


--
-- Name: soglie_idrometri_comune; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.soglie_idrometri_comune (
    id character varying NOT NULL,
    nome character varying,
    liv_arancione real,
    liv_rosso real
);


--
-- Name: sottopassi; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.sottopassi (
    gid integer NOT NULL,
    id_crit numeric(8,0) NOT NULL,
    nome_crit character varying(250),
    tipo_crit character varying(250),
    note_crit character varying(250),
    sot_ped_al character varying(20),
    sot_vei_al character varying(20),
    num_id character varying(20),
    municipio character varying(20),
    gestore character varying(250),
    rischio_idro character varying(20),
    peric_idro character varying(10),
    geom public.geometry(Point,3003) NOT NULL
);


--
-- Name: sottopassi_gid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.sottopassi_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: sottopassi_gid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.sottopassi_gid_seq OWNED BY geodb.sottopassi.gid;


--
-- Name: tracciato_stradale; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.tracciato_stradale (
    gid integer NOT NULL,
    codice_via character varying(5),
    nome_via character varying(150),
    geom public.geometry(MultiLineStringZ,3003)
);


--
-- Name: tracciato_stradale_gid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.tracciato_stradale_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tracciato_stradale_gid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.tracciato_stradale_gid_seq OWNED BY geodb.tracciato_stradale.gid;


--
-- Name: v_anagrafe; Type: VIEW; Schema: geodb; Owner: -
--

CREATE VIEW geodb.v_anagrafe AS
 SELECT a.codfisc,
    a.nome,
    a.cognome,
    a.matricola,
    a.famiglia,
    date_part('year'::text, age((to_date((a.datanas)::text, 'YYYYMMDD'::text))::timestamp with time zone)) AS anni,
    a.sesso,
    a.via,
    a.numciv,
    a.letciv,
    a.colciv,
    a.numint,
    a.letint,
    a.scala
   FROM geodb.anagrafe a;


--
-- Name: v_presidi_mobili; Type: VIEW; Schema: geodb; Owner: -
--

CREATE VIEW geodb.v_presidi_mobili AS
 SELECT presidi.percorso,
    public.st_multi(public.st_union(presidi.geom)) AS geom
   FROM geodb.presidi
  GROUP BY presidi.percorso
  ORDER BY presidi.percorso;


--
-- Name: v_vie_unite; Type: VIEW; Schema: geodb; Owner: -
--

CREATE VIEW geodb.v_vie_unite AS
 SELECT s.codvia,
    s.desvia,
    public.st_union(s.geom) AS geom
   FROM geodb.elemento_stradale s
  GROUP BY s.codvia, s.desvia;


--
-- Name: vie_wfs; Type: TABLE; Schema: geodb; Owner: -
--

CREATE TABLE geodb.vie_wfs (
    ogc_fid integer NOT NULL,
    wkb_geometry public.geometry(Geometry,3003),
    gml_id character varying,
    id_oggetto double precision,
    codvia character varying,
    desvia character varying,
    cod_tratta character varying,
    gestore character varying,
    classe_amministrativa character varying,
    classe_funzionale character varying,
    direzione character varying,
    stato character varying,
    tipo character varying,
    larghezza character varying,
    livello character varying,
    num_corsie double precision,
    fondo character varying,
    sede character varying,
    giunz_iniziale double precision,
    giunz_finale double precision,
    fittizia character varying,
    percorso character varying,
    prog_ini double precision,
    prog_fin double precision,
    reverse character varying,
    data_da character varying,
    data_a character varying,
    data_ins character varying,
    data_eli character varying,
    provenienza character varying,
    attend_geometrica character varying
);


--
-- Name: vie_ogc_fid_seq; Type: SEQUENCE; Schema: geodb; Owner: -
--

CREATE SEQUENCE geodb.vie_ogc_fid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: vie_ogc_fid_seq; Type: SEQUENCE OWNED BY; Schema: geodb; Owner: -
--

ALTER SEQUENCE geodb.vie_ogc_fid_seq OWNED BY geodb.vie_wfs.ogc_fid;


--
-- Name: mediatore:v_civici_dbt_angolo_geoserver; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public."mediatore:v_civici_dbt_angolo_geoserver" (
    ogc_fid integer NOT NULL,
    wkb_geometry public.geometry(Geometry,3003),
    gml_id character varying,
    id double precision,
    codvia character varying,
    numero character varying,
    lettera character varying,
    colore character varying,
    testo character varying,
    angolo double precision,
    data_da character varying,
    data_a character varying,
    codice_controllo character varying,
    machine_last_upd character varying,
    id_oggetto_riferimento double precision,
    id_asta character varying,
    codice_indirizzo double precision,
    tipo_accesso double precision,
    provenienza double precision,
    scala double precision,
    tipo_oggetto_riferimento character varying,
    uso character varying,
    desvia character varying,
    sezione_censimento_2011 double precision,
    sezione_elettorale double precision,
    data_ins character varying,
    sottotipo_accesso double precision
);


--
-- Name: mediatore:v_civici_dbt_angolo_geoserver_ogc_fid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public."mediatore:v_civici_dbt_angolo_geoserver_ogc_fid_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: mediatore:v_civici_dbt_angolo_geoserver_ogc_fid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public."mediatore:v_civici_dbt_angolo_geoserver_ogc_fid_seq" OWNED BY public."mediatore:v_civici_dbt_angolo_geoserver".ogc_fid;


--
-- Name: t_operatore_nverde; Type: TABLE; Schema: report; Owner: -
--

CREATE TABLE report.t_operatore_nverde (
    id integer NOT NULL,
    matricola_cf character varying NOT NULL,
    data_start timestamp without time zone NOT NULL,
    data_end timestamp without time zone NOT NULL,
    modificato boolean DEFAULT false,
    modifica character varying,
    warning_turno boolean
);


--
-- Name: doppioni_nverde; Type: VIEW; Schema: report; Owner: -
--

CREATE VIEW report.doppioni_nverde AS
 SELECT t_operatore_nverde.matricola_cf,
    t_operatore_nverde.data_start,
    count(*) AS count
   FROM report.t_operatore_nverde
  GROUP BY t_operatore_nverde.matricola_cf, t_operatore_nverde.data_start
 HAVING (count(*) > 1);


--
-- Name: t_aggiornamento_meteo; Type: TABLE; Schema: report; Owner: -
--

CREATE TABLE report.t_aggiornamento_meteo (
    id integer NOT NULL,
    id_evento integer NOT NULL,
    data timestamp without time zone NOT NULL,
    data_aggiornamento timestamp without time zone DEFAULT now() NOT NULL,
    aggiornamento character varying,
    allegati character varying
);


--
-- Name: t_aggiornamento_meteo_id_seq; Type: SEQUENCE; Schema: report; Owner: -
--

CREATE SEQUENCE report.t_aggiornamento_meteo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_aggiornamento_meteo_id_seq; Type: SEQUENCE OWNED BY; Schema: report; Owner: -
--

ALTER SEQUENCE report.t_aggiornamento_meteo_id_seq OWNED BY report.t_aggiornamento_meteo.id;


--
-- Name: t_comunicazione; Type: TABLE; Schema: report; Owner: -
--

CREATE TABLE report.t_comunicazione (
    id integer DEFAULT nextval('report.t_aggiornamento_meteo_id_seq'::regclass) NOT NULL,
    id_evento integer NOT NULL,
    data_aggiornamento timestamp without time zone DEFAULT now() NOT NULL,
    testo character varying,
    allegato character varying,
    modificato boolean DEFAULT false,
    modifica character varying
);


--
-- Name: t_coordinamento; Type: TABLE; Schema: report; Owner: -
--

CREATE TABLE report.t_coordinamento (
    id integer NOT NULL,
    matricola_cf character varying NOT NULL,
    data_start timestamp without time zone NOT NULL,
    data_end timestamp without time zone NOT NULL,
    modificato boolean DEFAULT false,
    modifica character varying,
    warning_turno boolean
);


--
-- Name: t_coordinamento_id_seq; Type: SEQUENCE; Schema: report; Owner: -
--

CREATE SEQUENCE report.t_coordinamento_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_coordinamento_id_seq; Type: SEQUENCE OWNED BY; Schema: report; Owner: -
--

ALTER SEQUENCE report.t_coordinamento_id_seq OWNED BY report.t_coordinamento.id;


--
-- Name: t_monitoraggio_meteo; Type: TABLE; Schema: report; Owner: -
--

CREATE TABLE report.t_monitoraggio_meteo (
    id integer NOT NULL,
    matricola_cf character varying NOT NULL,
    data_start timestamp without time zone NOT NULL,
    data_end timestamp without time zone NOT NULL,
    modificato boolean DEFAULT false,
    modifica character varying,
    warning_turno boolean
);


--
-- Name: t_monitoraggio_meteo_id_seq; Type: SEQUENCE; Schema: report; Owner: -
--

CREATE SEQUENCE report.t_monitoraggio_meteo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_monitoraggio_meteo_id_seq; Type: SEQUENCE OWNED BY; Schema: report; Owner: -
--

ALTER SEQUENCE report.t_monitoraggio_meteo_id_seq OWNED BY report.t_monitoraggio_meteo.id;


--
-- Name: t_operatore_anpas; Type: TABLE; Schema: report; Owner: -
--

CREATE TABLE report.t_operatore_anpas (
    id integer NOT NULL,
    matricola_cf character varying NOT NULL,
    data_start timestamp without time zone NOT NULL,
    data_end timestamp without time zone NOT NULL,
    modificato boolean DEFAULT false,
    modifica character varying,
    warning_turno boolean
);


--
-- Name: t_operatore_anpas_id_seq; Type: SEQUENCE; Schema: report; Owner: -
--

CREATE SEQUENCE report.t_operatore_anpas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_operatore_anpas_id_seq; Type: SEQUENCE OWNED BY; Schema: report; Owner: -
--

ALTER SEQUENCE report.t_operatore_anpas_id_seq OWNED BY report.t_operatore_anpas.id;


--
-- Name: t_operatore_nverde_id_seq; Type: SEQUENCE; Schema: report; Owner: -
--

CREATE SEQUENCE report.t_operatore_nverde_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_operatore_nverde_id_seq; Type: SEQUENCE OWNED BY; Schema: report; Owner: -
--

ALTER SEQUENCE report.t_operatore_nverde_id_seq OWNED BY report.t_operatore_nverde.id;


--
-- Name: t_tecnico_pc; Type: TABLE; Schema: report; Owner: -
--

CREATE TABLE report.t_tecnico_pc (
    id integer NOT NULL,
    matricola_cf character varying NOT NULL,
    data_start timestamp without time zone NOT NULL,
    data_end timestamp without time zone NOT NULL,
    modificato boolean DEFAULT false,
    modifica character varying,
    warning_turno boolean
);


--
-- Name: t_tecnico_pc_id_seq; Type: SEQUENCE; Schema: report; Owner: -
--

CREATE SEQUENCE report.t_tecnico_pc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_tecnico_pc_id_seq; Type: SEQUENCE OWNED BY; Schema: report; Owner: -
--

ALTER SEQUENCE report.t_tecnico_pc_id_seq OWNED BY report.t_tecnico_pc.id;


--
-- Name: t_operatore_volontari; Type: TABLE; Schema: report; Owner: -
--

CREATE TABLE report.t_operatore_volontari (
    id integer DEFAULT nextval('report.t_tecnico_pc_id_seq'::regclass) NOT NULL,
    matricola_cf character varying NOT NULL,
    data_start timestamp without time zone NOT NULL,
    data_end timestamp without time zone NOT NULL,
    modificato boolean DEFAULT false,
    modifica character varying,
    warning_turno boolean
);


--
-- Name: t_presidio_territoriale; Type: TABLE; Schema: report; Owner: -
--

CREATE TABLE report.t_presidio_territoriale (
    id integer NOT NULL,
    matricola_cf character varying NOT NULL,
    data_start timestamp without time zone NOT NULL,
    data_end timestamp without time zone NOT NULL,
    modificato boolean DEFAULT false,
    modifica character varying,
    warning_turno boolean
);


--
-- Name: t_presidio_territoriale_id_seq; Type: SEQUENCE; Schema: report; Owner: -
--

CREATE SEQUENCE report.t_presidio_territoriale_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_presidio_territoriale_id_seq; Type: SEQUENCE OWNED BY; Schema: report; Owner: -
--

ALTER SEQUENCE report.t_presidio_territoriale_id_seq OWNED BY report.t_presidio_territoriale.id;


--
-- Name: join_incarichi_interni_squadra; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.join_incarichi_interni_squadra (
    id_incarico integer NOT NULL,
    id_squadra integer NOT NULL,
    valido boolean DEFAULT true,
    data_ora timestamp without time zone DEFAULT now() NOT NULL,
    data_ora_cambio timestamp without time zone
);


--
-- Name: join_incarico_provvedimenti_cautelari; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.join_incarico_provvedimenti_cautelari (
    id integer NOT NULL,
    id_provvedimento integer NOT NULL,
    id_incarico integer NOT NULL
);


--
-- Name: join_incarico_provvedimenti_cautelari_id_seq; Type: SEQUENCE; Schema: segnalazioni; Owner: -
--

CREATE SEQUENCE segnalazioni.join_incarico_provvedimenti_cautelari_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: join_incarico_provvedimenti_cautelari_id_seq; Type: SEQUENCE OWNED BY; Schema: segnalazioni; Owner: -
--

ALTER SEQUENCE segnalazioni.join_incarico_provvedimenti_cautelari_id_seq OWNED BY segnalazioni.join_incarico_provvedimenti_cautelari.id;


--
-- Name: join_oggetto_rischio; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.join_oggetto_rischio (
    id_segnalazione integer NOT NULL,
    id_tipo_oggetto integer NOT NULL,
    id_oggetto integer NOT NULL,
    attivo boolean DEFAULT true NOT NULL,
    aggiornamento timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: join_segnalazioni_in_lavorazione; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.join_segnalazioni_in_lavorazione (
    id_segnalazione_in_lavorazione integer NOT NULL,
    id_segnalazione integer NOT NULL,
    sospeso boolean DEFAULT false NOT NULL
);


--
-- Name: join_segnalazioni_incarichi; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.join_segnalazioni_incarichi (
    id_incarico integer NOT NULL,
    id_segnalazione_in_lavorazione integer NOT NULL
);


--
-- Name: join_segnalazioni_incarichi_interni; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.join_segnalazioni_incarichi_interni (
    id_incarico integer NOT NULL,
    id_segnalazione_in_lavorazione integer NOT NULL
);


--
-- Name: join_segnalazioni_provvedimenti_cautelari; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.join_segnalazioni_provvedimenti_cautelari (
    id_provvedimento integer NOT NULL,
    id_segnalazione_in_lavorazione integer NOT NULL
);


--
-- Name: join_segnalazioni_sopralluoghi; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.join_segnalazioni_sopralluoghi (
    id_sopralluogo integer NOT NULL,
    id_segnalazione_in_lavorazione integer NOT NULL
);


--
-- Name: join_sopralluoghi_mobili_squadra; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.join_sopralluoghi_mobili_squadra (
    id_sopralluogo integer NOT NULL,
    id_squadra integer NOT NULL,
    valido boolean DEFAULT true,
    data_ora timestamp without time zone DEFAULT now() NOT NULL,
    data_ora_cambio timestamp without time zone
);


--
-- Name: join_sopralluoghi_squadra; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.join_sopralluoghi_squadra (
    id_sopralluogo integer NOT NULL,
    id_squadra integer NOT NULL,
    valido boolean DEFAULT true,
    data_ora timestamp without time zone DEFAULT now() NOT NULL,
    data_ora_cambio timestamp without time zone
);


--
-- Name: stato_incarichi; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.stato_incarichi (
    id_incarico integer NOT NULL,
    id_stato_incarico integer NOT NULL,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    parziale boolean DEFAULT false NOT NULL
);


--
-- Name: stato_incarichi_interni; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.stato_incarichi_interni (
    id_incarico integer NOT NULL,
    id_stato_incarico integer NOT NULL,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    parziale boolean DEFAULT false NOT NULL
);


--
-- Name: stato_provvedimenti_cautelari; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.stato_provvedimenti_cautelari (
    id_provvedimento integer NOT NULL,
    id_stato_provvedimenti_cautelari integer NOT NULL,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: stato_sopralluoghi; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.stato_sopralluoghi (
    id_sopralluogo integer NOT NULL,
    id_stato_sopralluogo integer NOT NULL,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    parziale boolean DEFAULT false NOT NULL
);


--
-- Name: stato_sopralluoghi_mobili; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.stato_sopralluoghi_mobili (
    id_sopralluogo integer NOT NULL,
    id_stato_sopralluogo integer NOT NULL,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    parziale boolean DEFAULT false NOT NULL
);


--
-- Name: t_comunicazioni_incarichi; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_comunicazioni_incarichi (
    id_incarico integer NOT NULL,
    testo character varying,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    allegato character varying
);


--
-- Name: t_comunicazioni_incarichi_interni; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_comunicazioni_incarichi_interni (
    id_incarico integer NOT NULL,
    testo character varying,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    allegato character varying
);


--
-- Name: t_comunicazioni_incarichi_interni_inviate; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_comunicazioni_incarichi_interni_inviate (
    id_incarico integer NOT NULL,
    testo character varying,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    allegato character varying
);


--
-- Name: t_comunicazioni_incarichi_inviate; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_comunicazioni_incarichi_inviate (
    id_incarico integer NOT NULL,
    testo character varying,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    allegato character varying
);


--
-- Name: t_comunicazioni_provvedimenti_cautelari; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_comunicazioni_provvedimenti_cautelari (
    id_provvedimento integer NOT NULL,
    testo character varying,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    allegato character varying
);


--
-- Name: t_comunicazioni_provvedimenti_cautelari_inviate; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_comunicazioni_provvedimenti_cautelari_inviate (
    id_provvedimento integer NOT NULL,
    testo character varying,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    allegato character varying
);


--
-- Name: t_comunicazioni_segnalazioni; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_comunicazioni_segnalazioni (
    id_lavorazione integer NOT NULL,
    mittente character varying,
    testo character varying,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    allegato character varying
);


--
-- Name: t_comunicazioni_segnalazioni_riservate; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_comunicazioni_segnalazioni_riservate (
    id_segnalazione integer NOT NULL,
    mittente character varying,
    testo character varying,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    allegato character varying
);


--
-- Name: t_comunicazioni_sopralluoghi; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_comunicazioni_sopralluoghi (
    id_sopralluogo integer NOT NULL,
    testo character varying,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    allegato character varying
);


--
-- Name: t_comunicazioni_sopralluoghi_inviate; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_comunicazioni_sopralluoghi_inviate (
    id_sopralluogo integer NOT NULL,
    testo character varying,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    allegato character varying
);


--
-- Name: t_comunicazioni_sopralluoghi_mobili; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_comunicazioni_sopralluoghi_mobili (
    id_sopralluogo integer NOT NULL,
    testo character varying,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    allegato character varying
);


--
-- Name: t_comunicazioni_sopralluoghi_mobili_inviate; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_comunicazioni_sopralluoghi_mobili_inviate (
    id_sopralluogo integer NOT NULL,
    testo character varying,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL,
    allegato character varying
);


--
-- Name: t_geometrie_provvedimenti_cautelari; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_geometrie_provvedimenti_cautelari (
    id_provvedimento integer NOT NULL,
    cod_via character varying,
    id_oggetto integer,
    tipo_oggetto character varying,
    id_civico_inizio integer,
    id_civico_fine integer,
    geom_inizio public.geometry(Point,4326),
    geom_fine public.geometry(Point,4326),
    geom public.geometry(LineStringZ,4326),
    descrizione character varying,
    codvia character varying
);


--
-- Name: t_incarichi; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_incarichi (
    id integer NOT NULL,
    data_ora_invio timestamp without time zone DEFAULT now() NOT NULL,
    id_profilo character varying NOT NULL,
    descrizione character varying NOT NULL,
    id_uo character varying NOT NULL,
    time_preview timestamp without time zone,
    time_start timestamp without time zone,
    time_stop timestamp without time zone,
    note_ente character varying,
    note_rifiuto character varying
);


--
-- Name: t_incarichi_interni; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_incarichi_interni (
    id integer NOT NULL,
    data_ora_invio timestamp without time zone DEFAULT now() NOT NULL,
    id_profilo character varying NOT NULL,
    descrizione character varying NOT NULL,
    id_squadra character varying NOT NULL,
    time_preview timestamp without time zone,
    time_start timestamp without time zone,
    time_stop timestamp without time zone,
    note_ente character varying,
    note_rifiuto character varying
);


--
-- Name: t_incarichi_interni_richiesta_cambi; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_incarichi_interni_richiesta_cambi (
    id_incarico integer NOT NULL,
    data_ora timestamp without time zone DEFAULT now() NOT NULL,
    eseguito boolean DEFAULT false
);


--
-- Name: t_ora_rimozione_provvedimenti_cautelari; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_ora_rimozione_provvedimenti_cautelari (
    id_provvedimento integer NOT NULL,
    data_ora_stato timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: t_provvedimenti_cautelari; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_provvedimenti_cautelari (
    id integer NOT NULL,
    data_ora_invio timestamp without time zone DEFAULT now() NOT NULL,
    id_profilo character varying NOT NULL,
    descrizione character varying NOT NULL,
    id_uo character varying NOT NULL,
    id_tipo integer NOT NULL,
    id_evento integer,
    time_preview timestamp without time zone,
    time_start timestamp without time zone,
    time_stop timestamp without time zone,
    note_ente character varying,
    note_rifiuto character varying,
    rimosso boolean DEFAULT false NOT NULL
);


--
-- Name: t_richieste_nverde; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_richieste_nverde (
    id integer NOT NULL,
    data_ora timestamp without time zone DEFAULT now() NOT NULL,
    id_segnalante integer NOT NULL,
    descrizione character varying NOT NULL,
    id_evento integer,
    id_operatore character varying NOT NULL,
    uo_ins character varying
);


--
-- Name: t_segnalanti; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_segnalanti (
    id integer NOT NULL,
    id_tipo_segnalante integer,
    altro_tipo character varying,
    nome_cognome character varying,
    telefono character varying,
    note character varying
);


--
-- Name: t_segnalanti_id_seq; Type: SEQUENCE; Schema: segnalazioni; Owner: -
--

CREATE SEQUENCE segnalazioni.t_segnalanti_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_segnalanti_id_seq; Type: SEQUENCE OWNED BY; Schema: segnalazioni; Owner: -
--

ALTER SEQUENCE segnalazioni.t_segnalanti_id_seq OWNED BY segnalazioni.t_segnalanti.id;


--
-- Name: t_segnalazioni; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_segnalazioni (
    id integer NOT NULL,
    data_ora timestamp without time zone DEFAULT now() NOT NULL,
    id_segnalante integer NOT NULL,
    descrizione character varying NOT NULL,
    id_criticita integer NOT NULL,
    rischio boolean,
    id_evento integer,
    id_civico integer,
    geom public.geometry(Point,4326) NOT NULL,
    id_municipio integer,
    id_operatore character varying NOT NULL,
    note character varying,
    uo_ins character varying,
    nverde boolean DEFAULT false NOT NULL
);


--
-- Name: t_segnalazioni_in_lavorazione; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_segnalazioni_in_lavorazione (
    id integer NOT NULL,
    in_lavorazione boolean DEFAULT true NOT NULL,
    id_profilo integer NOT NULL,
    invio_manutenzioni boolean,
    geom public.geometry(Point,4326),
    descrizione_chiusura character varying,
    id_man integer
);


--
-- Name: t_sopralluoghi; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_sopralluoghi (
    id integer NOT NULL,
    data_ora_invio timestamp without time zone DEFAULT now() NOT NULL,
    id_profilo character varying NOT NULL,
    descrizione character varying NOT NULL,
    time_preview timestamp without time zone,
    time_start timestamp without time zone,
    time_stop timestamp without time zone,
    note_ente character varying,
    geom public.geometry(Point,4326) NOT NULL,
    id_evento integer
);


--
-- Name: t_sopralluoghi_mobili; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_sopralluoghi_mobili (
    id integer NOT NULL,
    data_ora_invio timestamp without time zone DEFAULT now() NOT NULL,
    id_profilo character varying NOT NULL,
    descrizione character varying NOT NULL,
    time_preview timestamp without time zone,
    time_start timestamp without time zone,
    time_stop timestamp without time zone,
    note_ente character varying,
    geom public.geometry(MultiLineStringZ,4326) NOT NULL,
    id_evento integer
);


--
-- Name: t_sopralluoghi_mobili_richiesta_cambi; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_sopralluoghi_mobili_richiesta_cambi (
    id_sopralluogo integer NOT NULL,
    data_ora timestamp without time zone DEFAULT now() NOT NULL,
    eseguito boolean DEFAULT false
);


--
-- Name: t_sopralluoghi_richiesta_cambi; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_sopralluoghi_richiesta_cambi (
    id_sopralluogo integer NOT NULL,
    data_ora timestamp without time zone DEFAULT now() NOT NULL,
    eseguito boolean DEFAULT false
);


--
-- Name: t_spostamento_segnalazioni; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_spostamento_segnalazioni (
    id_segnalazione integer NOT NULL,
    data_ora_spostamento timestamp without time zone DEFAULT now() NOT NULL,
    old_geom public.geometry(Point,4326) NOT NULL
);


--
-- Name: t_storico_mail; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_storico_mail (
    id integer NOT NULL,
    data_ora timestamp(0) without time zone DEFAULT now() NOT NULL,
    id_uo1_mittente character varying NOT NULL,
    destinatario character varying NOT NULL,
    testo_aggiuntivo character varying,
    id_incarico integer NOT NULL
);


--
-- Name: t_storico_mail_id_seq; Type: SEQUENCE; Schema: segnalazioni; Owner: -
--

CREATE SEQUENCE segnalazioni.t_storico_mail_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_storico_mail_id_seq; Type: SEQUENCE OWNED BY; Schema: segnalazioni; Owner: -
--

ALTER SEQUENCE segnalazioni.t_storico_mail_id_seq OWNED BY segnalazioni.t_storico_mail.id;


--
-- Name: t_storico_segnalazioni_in_lavorazione; Type: TABLE; Schema: segnalazioni; Owner: -
--

CREATE TABLE segnalazioni.t_storico_segnalazioni_in_lavorazione (
    id_segnalazione_in_lavorazione integer NOT NULL,
    log_aggiornamento character varying,
    data_ora timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: v_segnalazioni; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_segnalazioni AS
 SELECT s.id,
    to_char(s.data_ora, 'YYYY/MM/DD HH24:MI'::text) AS data_ora,
    s.id_segnalante,
    s.descrizione,
    s.id_criticita,
    c.descrizione AS criticita,
    s.rischio,
    s.id_evento,
        CASE
            WHEN (e.valido = true) THEN 'Evento attivo'::text
            ELSE 'Evento in chiusura'::text
        END AS tipo_evento,
    s.id_civico,
    s.geom,
    s.id_municipio,
    s.id_operatore,
    s.note,
    jl.id_segnalazione_in_lavorazione AS id_lavorazione,
    l.in_lavorazione,
    l.id_profilo,
    l.descrizione_chiusura,
    s.uo_ins,
    e.fine_sospensione
   FROM ((((segnalazioni.t_segnalazioni s
     JOIN segnalazioni.tipo_criticita c ON ((c.id = s.id_criticita)))
     JOIN eventi.t_eventi e ON ((e.id = s.id_evento)))
     LEFT JOIN segnalazioni.join_segnalazioni_in_lavorazione jl ON ((jl.id_segnalazione = s.id)))
     LEFT JOIN segnalazioni.t_segnalazioni_in_lavorazione l ON ((jl.id_segnalazione_in_lavorazione = l.id)))
  WHERE ((e.valido = true) OR (e.valido IS NULL))
  ORDER BY s.id_evento, l.in_lavorazione DESC;


--
-- Name: profili_utilizzatore; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.profili_utilizzatore (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: t_mail_incarichi; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.t_mail_incarichi (
    cod character varying NOT NULL,
    mail character varying NOT NULL,
    matricola_cf character varying,
    id_telegram character varying
);


--
-- Name: uo_1_livello; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.uo_1_livello (
    id1 integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL,
    invio_incarichi boolean DEFAULT true NOT NULL,
    reperibilita boolean DEFAULT false NOT NULL
);


--
-- Name: t_incarichi_comune; Type: VIEW; Schema: varie; Owner: -
--

CREATE VIEW varie.t_incarichi_comune AS
 SELECT 'PC'::text AS cod,
    (p.id)::text AS profilo,
    'Protezione civile'::text AS descrizione,
    p.valido
   FROM users.profili_utilizzatore p
  WHERE (p.id = 3)
UNION
 SELECT 'COA'::text AS cod,
    (p.id)::text AS profilo,
    'Centrale COA'::text AS descrizione,
    p.valido
   FROM users.profili_utilizzatore p
  WHERE (p.id = 4)
UNION
 SELECT ('MU00'::text || (m.codice_mun)::text) AS cod,
    (((p.id)::text || '_'::text) || (m.codice_mun)::text) AS profilo,
    ('Municipio '::text || (m.nome_munic)::text) AS descrizione,
    p.valido
   FROM users.profili_utilizzatore p,
    geodb.municipi m
  WHERE (p.id = 5)
UNION
 SELECT ('PO'::text || (m.codice_mun)::text) AS cod,
    (((p.id)::text || '_'::text) || (m.codice_mun)::text) AS profilo,
    ('Distretto '::text || (m.codice_mun)::text) AS descrizione,
    p.valido
   FROM users.profili_utilizzatore p,
    geodb.municipi m
  WHERE (p.id = 6)
UNION
 SELECT ('COC'::text || (m.id)::text) AS cod,
    (((p.id)::text || '_'::text) || (m.id)::text) AS profilo,
    (m.descrizione)::text AS descrizione,
    p.valido
   FROM users.profili_utilizzatore p,
    users.tipo_coc_interno m
  WHERE (p.id = 7)
  ORDER BY 1;


--
-- Name: v_incarichi_mail_input; Type: VIEW; Schema: varie; Owner: -
--

CREATE VIEW varie.v_incarichi_mail_input AS
 SELECT concat('com_', t_incarichi_comune.cod) AS cod,
    (t_incarichi_comune.descrizione)::character varying AS descrizione,
    'interni'::text AS tipo,
    (t_incarichi_comune.profilo)::character varying AS profilo
   FROM varie.t_incarichi_comune
UNION
 SELECT concat('uo_', uo_1_livello.id1) AS cod,
    uo_1_livello.descrizione,
    'esterni'::text AS tipo,
    concat('uo_', uo_1_livello.id1) AS profilo
   FROM users.uo_1_livello
  ORDER BY 3, 2;


--
-- Name: v_incarichi_mail_old; Type: VIEW; Schema: varie; Owner: -
--

CREATE VIEW varie.v_incarichi_mail_old AS
 SELECT i.cod,
    i.descrizione,
    i.tipo,
    string_agg((m.mail)::text, ' - '::text) AS mails,
    i.profilo,
    m.id_telegram
   FROM (varie.v_incarichi_mail_input i
     LEFT JOIN users.t_mail_incarichi m ON ((i.cod = (m.cod)::text)))
  GROUP BY i.cod, i.descrizione, i.tipo, i.profilo, m.id_telegram;


--
-- Name: v_provvedimenti_cautelari; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_provvedimenti_cautelari AS
 SELECT i.id,
    to_char(i.data_ora_invio, 'YYYY/MM/DD HH24:MI:SS'::text) AS data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_uo AS id_squadra,
    u.descrizione AS descrizione_uo,
    to_char(i.time_preview, 'YYYY/MM/DD HH24:MI'::text) AS time_preview,
    to_char(i.time_start, 'YYYY/MM/DD HH24:MI'::text) AS time_start,
    to_char(i.time_stop, 'YYYY/MM/DD HH24:MI'::text) AS time_stop,
    i.note_ente,
    g.cod_via,
    g.id_oggetto,
        CASE
            WHEN ((g.tipo_oggetto)::text = 'geodb.sottopassi'::text) THEN (ss.nome_crit)::text
            WHEN ((g.tipo_oggetto)::text = 'geodb.civici'::text) THEN (((cc.desvia)::text || ' '::text) || (cc.testo)::text)
            WHEN ((g.tipo_oggetto)::text = 'geodb.v_vie_unite'::text) THEN (vv.desvia)::text
            ELSE NULL::text
        END AS oggetto,
    g.tipo_oggetto,
    g.id_civico_inizio,
    g.id_civico_fine,
    g.geom_inizio,
    g.geom,
    g.descrizione AS desc_via,
    g.codvia,
    s.id AS id_segnalazione,
    s.id_lavorazione,
    i.id_evento,
    s.descrizione AS descrizione_segnalazione,
    s.criticita,
    s.rischio,
    st.id_stato_provvedimenti_cautelari,
    t.descrizione AS descrizione_stato,
    st.data_ora_stato,
    tt.descrizione AS tipo_provvedimento,
    i.rimosso,
    tt.id AS id_tipo_pc
   FROM (((((((((((segnalazioni.t_provvedimenti_cautelari i
     JOIN segnalazioni.stato_provvedimenti_cautelari st ON ((st.id_provvedimento = i.id)))
     JOIN segnalazioni.tipo_stato_provvedimenti_cautelari t ON ((t.id = st.id_stato_provvedimenti_cautelari)))
     LEFT JOIN segnalazioni.join_segnalazioni_provvedimenti_cautelari j ON ((i.id = j.id_provvedimento)))
     LEFT JOIN segnalazioni.v_segnalazioni s ON ((s.id_lavorazione = j.id_segnalazione_in_lavorazione)))
     JOIN segnalazioni.t_geometrie_provvedimenti_cautelari g ON ((i.id = g.id_provvedimento)))
     LEFT JOIN geodb.sottopassi ss ON ((ss.id_crit = (g.id_oggetto)::numeric)))
     LEFT JOIN geodb.civici cc ON ((cc.id = g.id_oggetto)))
     LEFT JOIN geodb.v_vie_unite vv ON (((vv.codvia)::text = lpad((g.id_oggetto)::text, 5, '0'::text))))
     JOIN varie.v_incarichi_mail_old u ON ((u.cod = (i.id_uo)::text)))
     JOIN eventi.t_eventi e ON ((e.id = i.id_evento)))
     JOIN segnalazioni.tipo_provvedimenti_cautelari tt ON ((tt.id = i.id_tipo)))
  WHERE ((e.valido = true) OR (e.valido IS NULL))
UNION
 SELECT i.id,
    to_char(i.data_ora_invio, 'YYYY/MM/DD HH24:MI:SS'::text) AS data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_uo AS id_squadra,
    u.descrizione AS descrizione_uo,
    to_char(i.time_preview, 'YYYY/MM/DD HH24:MI'::text) AS time_preview,
    to_char(i.time_start, 'YYYY/MM/DD HH24:MI'::text) AS time_start,
    to_char(i.time_stop, 'YYYY/MM/DD HH24:MI'::text) AS time_stop,
    i.note_ente,
    g.cod_via,
    g.id_oggetto,
        CASE
            WHEN ((g.tipo_oggetto)::text = 'geodb.sottopassi'::text) THEN (ss.nome_crit)::text
            WHEN ((g.tipo_oggetto)::text = 'geodb.civici'::text) THEN (((cc.desvia)::text || ' '::text) || (cc.testo)::text)
            WHEN ((g.tipo_oggetto)::text = 'geodb.v_vie_unite'::text) THEN (vv.desvia)::text
            ELSE NULL::text
        END AS oggetto,
    g.tipo_oggetto,
    g.id_civico_inizio,
    g.id_civico_fine,
    g.geom_inizio,
    g.geom,
    g.descrizione AS desc_via,
    g.codvia,
    s.id AS id_segnalazione,
    s.id_lavorazione,
    i.id_evento,
    s.descrizione AS descrizione_segnalazione,
    s.criticita,
    s.rischio,
    st.id_stato_provvedimenti_cautelari,
    t.descrizione AS descrizione_stato,
    st.data_ora_stato,
    tt.descrizione AS tipo_provvedimento,
    i.rimosso,
    tt.id AS id_tipo_pc
   FROM (((((((((((segnalazioni.t_provvedimenti_cautelari i
     JOIN segnalazioni.stato_provvedimenti_cautelari st ON ((st.id_provvedimento = i.id)))
     JOIN segnalazioni.tipo_stato_provvedimenti_cautelari t ON ((t.id = st.id_stato_provvedimenti_cautelari)))
     LEFT JOIN segnalazioni.join_segnalazioni_provvedimenti_cautelari j ON ((i.id = j.id_provvedimento)))
     LEFT JOIN segnalazioni.v_segnalazioni s ON ((s.id_lavorazione = j.id_segnalazione_in_lavorazione)))
     JOIN segnalazioni.t_geometrie_provvedimenti_cautelari g ON ((i.id = g.id_provvedimento)))
     LEFT JOIN geodb.sottopassi ss ON ((ss.id_crit = (g.id_oggetto)::numeric)))
     LEFT JOIN geodb.civici cc ON ((cc.id = g.id_oggetto)))
     LEFT JOIN geodb.v_vie_unite vv ON (((vv.codvia)::text = lpad((g.id_oggetto)::text, 5, '0'::text))))
     JOIN users.tipo_origine_provvedimenti_cautelari u ON (((u.id)::text = (i.id_uo)::text)))
     JOIN eventi.t_eventi e ON ((e.id = i.id_evento)))
     JOIN segnalazioni.tipo_provvedimenti_cautelari tt ON ((tt.id = i.id_tipo)))
  WHERE ((e.valido = true) OR (e.valido IS NULL));


--
-- Name: v_incarichi; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_incarichi AS
 SELECT i.id,
    to_char(i.data_ora_invio, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_uo,
    u.descrizione AS descrizione_uo,
    to_char(i.time_preview, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_preview,
    to_char(i.time_start, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_start,
    to_char(i.time_stop, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_stop,
    i.note_ente,
    i.note_rifiuto,
    s.geom,
    s.id AS id_segnalazione,
    s.id_lavorazione,
    s.id_evento,
    s.descrizione AS descrizione_segnalazione,
    s.criticita,
    s.rischio,
    st.id_stato_incarico,
    t.descrizione AS descrizione_stato,
    st.data_ora_stato,
    st.parziale,
    NULL::integer AS id_pc
   FROM ((((((segnalazioni.t_incarichi i
     JOIN segnalazioni.join_segnalazioni_incarichi j ON ((j.id_incarico = i.id)))
     JOIN segnalazioni.stato_incarichi st ON ((st.id_incarico = i.id)))
     JOIN segnalazioni.tipo_stato_incarichi t ON ((t.id = st.id_stato_incarico)))
     JOIN segnalazioni.v_segnalazioni s ON ((s.id_lavorazione = j.id_segnalazione_in_lavorazione)))
     JOIN varie.v_incarichi_mail_old u ON ((u.cod = (i.id_uo)::text)))
     JOIN eventi.t_eventi e ON ((e.id = s.id_evento)))
  WHERE ((e.valido = true) OR (e.valido IS NULL))
UNION
 SELECT i.id,
    to_char(i.data_ora_invio, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_uo,
    u.descrizione AS descrizione_uo,
    to_char(i.time_preview, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_preview,
    to_char(i.time_start, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_start,
    to_char(i.time_stop, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_stop,
    i.note_ente,
    i.note_rifiuto,
        CASE
            WHEN ((pc1.geom_inizio)::text <> ''::text) THEN pc1.geom_inizio
            ELSE (public.st_centroid(pc1.geom))::public.geometry(Point,4326)
        END AS geom,
    NULL::integer AS id_segnalazione,
    NULL::integer AS id_lavorazione,
    pc1.id_evento,
    NULL::character varying AS descrizione_segnalazione,
    NULL::character varying AS criticita,
    NULL::boolean AS rischio,
    st.id_stato_incarico,
    t.descrizione AS descrizione_stato,
    st.data_ora_stato,
    st.parziale,
    pc1.id AS id_pc
   FROM ((((((segnalazioni.t_incarichi i
     JOIN segnalazioni.join_incarico_provvedimenti_cautelari pc ON ((pc.id_incarico = i.id)))
     JOIN segnalazioni.stato_incarichi st ON ((st.id_incarico = i.id)))
     JOIN segnalazioni.tipo_stato_incarichi t ON ((t.id = st.id_stato_incarico)))
     JOIN segnalazioni.v_provvedimenti_cautelari pc1 ON ((pc.id_provvedimento = pc1.id)))
     JOIN varie.v_incarichi_mail_old u ON ((u.cod = (i.id_uo)::text)))
     JOIN eventi.t_eventi e ON ((e.id = pc1.id_evento)))
  WHERE ((e.valido = true) OR (e.valido IS NULL));


--
-- Name: v_segnalazioni_eventi_chiusi_lista; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_segnalazioni_eventi_chiusi_lista AS
 SELECT s.id,
    to_char(s.data_ora, 'YYYY/MM/DD HH24:MM'::text) AS data_ora,
    s.id_segnalante,
    s.descrizione,
    s.id_criticita,
    c.descrizione AS criticita,
    s.rischio,
    s.id_evento,
        CASE
            WHEN (e.valido = true) THEN 'Evento'::text
            ELSE 'Evento chiuso'::text
        END AS tipo_evento,
    s.id_civico,
        CASE
            WHEN (s.id_civico IS NULL) THEN 'n.d.'::text
            ELSE (((g.desvia)::text || ' '::text) || (g.testo)::text)
        END AS localizzazione,
    s.geom,
    s.id_municipio,
    m.nome_munic,
    s.id_operatore,
    s.note,
    jl.id_segnalazione_in_lavorazione AS id_lavorazione,
    l.in_lavorazione,
    l.id_profilo,
    l.descrizione_chiusura,
    s.uo_ins,
    l.id_man
   FROM ((((((segnalazioni.t_segnalazioni s
     JOIN segnalazioni.tipo_criticita c ON ((c.id = s.id_criticita)))
     JOIN eventi.t_eventi e ON ((e.id = s.id_evento)))
     LEFT JOIN segnalazioni.join_segnalazioni_in_lavorazione jl ON ((jl.id_segnalazione = s.id)))
     LEFT JOIN segnalazioni.t_segnalazioni_in_lavorazione l ON ((jl.id_segnalazione_in_lavorazione = l.id)))
     LEFT JOIN geodb.municipi m ON ((s.id_municipio = (m.id)::integer)))
     LEFT JOIN geodb.civici g ON ((g.id = s.id_civico)))
  WHERE (e.valido = false)
  ORDER BY s.id_evento, l.in_lavorazione DESC;


--
-- Name: v_incarichi_eventi_chiusi; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_incarichi_eventi_chiusi AS
 SELECT i.id,
    to_char(i.data_ora_invio, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_uo,
    u.descrizione AS descrizione_uo,
    to_char(i.time_preview, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_preview,
    to_char(i.time_start, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_start,
    to_char(i.time_stop, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_stop,
    i.note_ente,
    i.note_rifiuto,
    s.geom,
    s.id AS id_segnalazione,
    s.id_lavorazione,
    s.id_evento,
    s.descrizione AS descrizione_segnalazione,
    s.criticita,
    s.rischio,
    st.id_stato_incarico,
    t.descrizione AS descrizione_stato,
    st.data_ora_stato,
    st.parziale
   FROM ((((((segnalazioni.t_incarichi i
     JOIN segnalazioni.join_segnalazioni_incarichi j ON ((j.id_incarico = i.id)))
     JOIN segnalazioni.stato_incarichi st ON ((st.id_incarico = i.id)))
     JOIN segnalazioni.tipo_stato_incarichi t ON ((t.id = st.id_stato_incarico)))
     LEFT JOIN segnalazioni.v_segnalazioni_eventi_chiusi_lista s ON ((s.id_lavorazione = j.id_segnalazione_in_lavorazione)))
     JOIN varie.v_incarichi_mail_old u ON ((u.cod = (i.id_uo)::text)))
     JOIN eventi.t_eventi e ON ((e.id = s.id_evento)))
  WHERE (e.valido = false);


--
-- Name: v_incarichi_eventi_chiusi_last_update; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_incarichi_eventi_chiusi_last_update AS
 SELECT i.id,
    i.data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_uo,
    i.descrizione_uo,
    i.time_preview,
    i.time_start,
    i.time_stop,
    i.note_ente,
    i.note_rifiuto,
    i.geom,
    i.id_segnalazione,
    i.id_evento,
    i.id_lavorazione,
    i.descrizione_segnalazione,
    i.criticita,
    i.rischio,
    i.id_stato_incarico,
    i.descrizione_stato,
    i.data_ora_stato,
    i.parziale
   FROM segnalazioni.v_incarichi_eventi_chiusi i
  WHERE (i.data_ora_stato = ( SELECT max(v_incarichi_eventi_chiusi.data_ora_stato) AS max
           FROM segnalazioni.v_incarichi_eventi_chiusi
          WHERE (v_incarichi_eventi_chiusi.id = i.id)))
  ORDER BY i.id;


--
-- Name: t_componenti_squadre; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.t_componenti_squadre (
    id_squadra integer NOT NULL,
    matricola_cf character varying NOT NULL,
    capo_squadra boolean DEFAULT false NOT NULL,
    data_start timestamp without time zone DEFAULT now() NOT NULL,
    data_end timestamp without time zone
);


--
-- Name: t_squadre; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.t_squadre (
    id integer NOT NULL,
    nome character varying NOT NULL,
    id_evento integer,
    id_stato integer NOT NULL,
    cod_afferenza character varying NOT NULL,
    da_nascondere boolean DEFAULT false NOT NULL
);


--
-- Name: COLUMN t_squadre.da_nascondere; Type: COMMENT; Schema: users; Owner: -
--

COMMENT ON COLUMN users.t_squadre.da_nascondere IS 'Booleano da usarsi per nascondere la squadra nel caso di eventi futuri (solo ammninistratori di sistema)';


--
-- Name: t_stato_squadre; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.t_stato_squadre (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: t_afferenza_squadre; Type: TABLE; Schema: varie; Owner: -
--

CREATE TABLE varie.t_afferenza_squadre (
    cod character varying,
    descrizione character varying
);


--
-- Name: v_squadre_all; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_squadre_all WITH (security_barrier='false') AS
 SELECT s.id,
    s.nome,
    ((((e.descrizione)::text || ' ('::text) || s.id_evento) || ')'::text) AS evento,
    s.id_stato,
    st.descrizione AS stato,
    s.cod_afferenza,
    a.descrizione AS afferenza,
    ( SELECT count(c.id_squadra) AS count
           FROM users.t_componenti_squadre c
          WHERE ((c.id_squadra = s.id) AND (c.data_end IS NULL))) AS num_componenti
   FROM (((users.t_squadre s
     LEFT JOIN varie.t_afferenza_squadre a ON (((s.cod_afferenza)::text = (a.cod)::text)))
     LEFT JOIN eventi.v_eventi e ON ((s.id_evento = e.id)))
     JOIN users.t_stato_squadre st ON ((s.id_stato = st.id)));


--
-- Name: v_incarichi_interni; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_incarichi_interni AS
 SELECT i.id,
    to_char(i.data_ora_invio, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_squadra,
    u.nome AS descrizione_uo,
    to_char(i.time_preview, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_preview,
    to_char(i.time_start, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_start,
    to_char(i.time_stop, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_stop,
    i.note_ente,
    i.note_rifiuto,
    s.geom,
    s.id AS id_segnalazione,
    s.id_lavorazione,
    s.id_evento,
    s.descrizione AS descrizione_segnalazione,
    s.criticita,
    s.rischio,
    st.id_stato_incarico,
    t.descrizione AS descrizione_stato,
    st.data_ora_stato,
    st.parziale
   FROM ((((((segnalazioni.t_incarichi_interni i
     JOIN segnalazioni.join_segnalazioni_incarichi_interni j ON ((j.id_incarico = i.id)))
     JOIN segnalazioni.stato_incarichi_interni st ON ((st.id_incarico = i.id)))
     JOIN segnalazioni.tipo_stato_incarichi t ON ((t.id = st.id_stato_incarico)))
     LEFT JOIN segnalazioni.v_segnalazioni s ON ((s.id_lavorazione = j.id_segnalazione_in_lavorazione)))
     JOIN users.v_squadre_all u ON (((u.id)::text = (i.id_squadra)::text)))
     JOIN eventi.t_eventi e ON ((e.id = s.id_evento)))
  WHERE ((e.valido = true) OR (e.valido IS NULL));


--
-- Name: v_incarichi_interni_eventi_chiusi; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_incarichi_interni_eventi_chiusi AS
 SELECT i.id,
    to_char(i.data_ora_invio, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_squadra,
    u.nome AS descrizione_uo,
    to_char(i.time_preview, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_preview,
    to_char(i.time_start, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_start,
    to_char(i.time_stop, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_stop,
    i.note_ente,
    i.note_rifiuto,
    s.geom,
    s.id AS id_segnalazione,
    s.id_lavorazione,
    s.id_evento,
    s.descrizione AS descrizione_segnalazione,
    s.criticita,
    s.rischio,
    st.id_stato_incarico,
    t.descrizione AS descrizione_stato,
    st.data_ora_stato,
    st.parziale
   FROM ((((((segnalazioni.t_incarichi_interni i
     JOIN segnalazioni.join_segnalazioni_incarichi_interni j ON ((j.id_incarico = i.id)))
     JOIN segnalazioni.stato_incarichi_interni st ON ((st.id_incarico = i.id)))
     JOIN segnalazioni.tipo_stato_incarichi t ON ((t.id = st.id_stato_incarico)))
     LEFT JOIN segnalazioni.v_segnalazioni_eventi_chiusi_lista s ON ((s.id_lavorazione = j.id_segnalazione_in_lavorazione)))
     JOIN users.v_squadre_all u ON (((u.id)::text = (i.id_squadra)::text)))
     JOIN eventi.t_eventi e ON ((e.id = s.id_evento)))
  WHERE (e.valido = false);


--
-- Name: v_incarichi_interni_eventi_chiusi_last_update; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_incarichi_interni_eventi_chiusi_last_update AS
 SELECT i.id,
    i.data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_squadra,
    i.descrizione_uo,
    i.time_preview,
    i.time_start,
    i.time_stop,
    i.note_ente,
    i.note_rifiuto,
    i.geom,
    i.id_segnalazione,
    i.id_evento,
    i.id_lavorazione,
    i.descrizione_segnalazione,
    i.criticita,
    i.rischio,
    i.id_stato_incarico,
    i.descrizione_stato,
    i.data_ora_stato,
    i.parziale
   FROM segnalazioni.v_incarichi_interni_eventi_chiusi i
  WHERE (i.data_ora_stato = ( SELECT max(v_incarichi_interni_eventi_chiusi.data_ora_stato) AS max
           FROM segnalazioni.v_incarichi_interni_eventi_chiusi
          WHERE (v_incarichi_interni_eventi_chiusi.id = i.id)))
  ORDER BY i.id;


--
-- Name: v_incarichi_interni_last_update; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_incarichi_interni_last_update AS
 SELECT i.id,
    i.data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_squadra,
    i.descrizione_uo,
    i.time_preview,
    i.time_start,
    i.time_stop,
    i.note_ente,
    i.note_rifiuto,
    i.geom,
    i.id_segnalazione,
    i.id_evento,
    i.id_lavorazione,
    i.descrizione_segnalazione,
    i.criticita,
    i.rischio,
    i.id_stato_incarico,
    i.descrizione_stato,
    i.data_ora_stato,
    i.parziale
   FROM segnalazioni.v_incarichi_interni i
  WHERE (i.data_ora_stato = ( SELECT max(v_incarichi_interni.data_ora_stato) AS max
           FROM segnalazioni.v_incarichi_interni
          WHERE (v_incarichi_interni.id = i.id)))
  ORDER BY i.id;


--
-- Name: v_incarichi_last_update; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_incarichi_last_update AS
 SELECT i.id,
    i.data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_uo,
    i.descrizione_uo,
    i.time_preview,
    i.time_start,
    i.time_stop,
    i.note_ente,
    i.note_rifiuto,
    i.geom,
    i.id_segnalazione,
    i.id_evento,
    i.id_lavorazione,
    i.descrizione_segnalazione,
    i.criticita,
    i.rischio,
    i.id_stato_incarico,
    i.descrizione_stato,
    i.data_ora_stato,
    i.parziale
   FROM segnalazioni.v_incarichi i
  WHERE (i.data_ora_stato = ( SELECT max(v_incarichi.data_ora_stato) AS max
           FROM segnalazioni.v_incarichi
          WHERE (v_incarichi.id = i.id)))
  ORDER BY i.id;


--
-- Name: v_provvedimenti_cautelari_eventi_chiusi; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_provvedimenti_cautelari_eventi_chiusi AS
 SELECT i.id,
    to_char(i.data_ora_invio, 'YYYY/MM/DD HH24:MI:SS'::text) AS data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_uo AS id_squadra,
    u.descrizione AS descrizione_uo,
    to_char(i.time_preview, 'YYYY/MM/DD HH24:MI:SS'::text) AS time_preview,
    to_char(i.time_start, 'YYYY/MM/DD HH24:MI:SS'::text) AS time_start,
    to_char(i.time_stop, 'YYYY/MM/DD HH24:MI:SS'::text) AS time_stop,
    i.note_ente,
    g.cod_via,
    g.id_oggetto,
    g.tipo_oggetto,
    g.id_civico_inizio,
    g.id_civico_fine,
    g.geom_inizio,
    g.geom_fine,
    s.id AS id_segnalazione,
    s.id_lavorazione,
    i.id_evento,
    s.descrizione AS descrizione_segnalazione,
    s.criticita,
    s.rischio,
    st.id_stato_provvedimenti_cautelari,
    t.descrizione AS descrizione_stato,
    st.data_ora_stato,
    tt.descrizione AS tipo_provvedimento,
    i.rimosso,
        CASE
            WHEN ((g.tipo_oggetto)::text = 'geodb.sottopassi'::text) THEN (ss.nome_crit)::text
            WHEN ((g.tipo_oggetto)::text = 'geodb.civici'::text) THEN (((cc.desvia)::text || ' '::text) || (cc.testo)::text)
            WHEN ((g.tipo_oggetto)::text = 'geodb.v_vie_unite'::text) THEN (vv.desvia)::text
            ELSE NULL::text
        END AS oggetto
   FROM (((((((((((segnalazioni.t_provvedimenti_cautelari i
     JOIN segnalazioni.stato_provvedimenti_cautelari st ON ((st.id_provvedimento = i.id)))
     JOIN segnalazioni.tipo_stato_provvedimenti_cautelari t ON ((t.id = st.id_stato_provvedimenti_cautelari)))
     JOIN segnalazioni.t_geometrie_provvedimenti_cautelari g ON ((i.id = g.id_provvedimento)))
     LEFT JOIN segnalazioni.join_segnalazioni_provvedimenti_cautelari j ON ((i.id = j.id_provvedimento)))
     LEFT JOIN segnalazioni.v_segnalazioni_eventi_chiusi_lista s ON ((s.id_lavorazione = j.id_segnalazione_in_lavorazione)))
     LEFT JOIN geodb.sottopassi ss ON ((ss.id_crit = (g.id_oggetto)::numeric)))
     LEFT JOIN geodb.civici cc ON ((cc.id = g.id_oggetto)))
     LEFT JOIN geodb.v_vie_unite vv ON (((vv.codvia)::text = lpad((g.id_oggetto)::text, 5, '0'::text))))
     JOIN varie.v_incarichi_mail_old u ON ((u.cod = (i.id_uo)::text)))
     JOIN eventi.t_eventi e ON ((e.id = i.id_evento)))
     JOIN segnalazioni.tipo_provvedimenti_cautelari tt ON ((tt.id = i.id_tipo)))
  WHERE (e.valido = false);


--
-- Name: v_provvedimenti_cautelari_eventi_chiusi_last_update; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_provvedimenti_cautelari_eventi_chiusi_last_update AS
 SELECT i.id,
    i.data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_squadra,
    i.descrizione_uo,
    i.time_preview,
    i.time_start,
    i.time_stop,
    i.note_ente,
    i.geom_inizio,
    i.geom_fine,
    i.cod_via,
    i.id_oggetto,
    i.tipo_oggetto,
    i.id_civico_inizio,
    i.id_civico_fine,
    i.id_segnalazione,
    i.id_evento,
    i.id_lavorazione,
    i.descrizione_segnalazione,
    i.criticita,
    i.rischio,
    i.id_stato_provvedimenti_cautelari,
    i.descrizione_stato,
    i.data_ora_stato,
    i.tipo_provvedimento,
    i.rimosso,
    i.oggetto
   FROM segnalazioni.v_provvedimenti_cautelari_eventi_chiusi i
  WHERE ((i.data_ora_stato = ( SELECT max(v_provvedimenti_cautelari_eventi_chiusi.data_ora_stato) AS max
           FROM segnalazioni.v_provvedimenti_cautelari_eventi_chiusi
          WHERE (v_provvedimenti_cautelari_eventi_chiusi.id = i.id))) AND ((i.id_segnalazione = ( SELECT min(v_provvedimenti_cautelari_eventi_chiusi.id_segnalazione) AS min
           FROM segnalazioni.v_provvedimenti_cautelari_eventi_chiusi
          WHERE (v_provvedimenti_cautelari_eventi_chiusi.id = i.id))) OR (i.id_segnalazione IS NULL)))
  ORDER BY i.id;


--
-- Name: v_provvedimenti_cautelari_last_update; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_provvedimenti_cautelari_last_update AS
 SELECT i.id,
    i.data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_squadra,
    i.descrizione_uo,
    i.time_preview,
    i.time_start,
    i.time_stop,
    i.note_ente,
    i.geom_inizio,
    i.geom,
    i.desc_via,
    i.codvia,
    i.cod_via,
    i.id_oggetto,
    i.tipo_oggetto,
    i.oggetto,
    i.id_civico_inizio,
    i.id_civico_fine,
    i.id_segnalazione,
    i.id_evento,
    i.id_lavorazione,
    i.descrizione_segnalazione,
    i.criticita,
    i.rischio,
    i.id_stato_provvedimenti_cautelari,
    i.descrizione_stato,
    i.data_ora_stato,
    i.tipo_provvedimento,
    i.rimosso,
    i.id_tipo_pc
   FROM segnalazioni.v_provvedimenti_cautelari i
  WHERE ((i.data_ora_stato = ( SELECT max(v_provvedimenti_cautelari.data_ora_stato) AS max
           FROM segnalazioni.v_provvedimenti_cautelari
          WHERE (v_provvedimenti_cautelari.id = i.id))) AND ((i.id_segnalazione = ( SELECT min(v_provvedimenti_cautelari.id_segnalazione) AS min
           FROM segnalazioni.v_provvedimenti_cautelari
          WHERE (v_provvedimenti_cautelari.id = i.id))) OR (i.id_segnalazione IS NULL)))
  ORDER BY i.id;


--
-- Name: v_sopralluoghi; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_sopralluoghi AS
 SELECT i.id,
    to_char(i.data_ora_invio, 'DD/MM/YY HH24:MI:SS'::text) AS data_ora_invio,
    i.id_profilo,
    i.descrizione,
    sq.id_squadra,
    u.nome AS descrizione_uo,
    to_char(i.time_preview, 'DD/MM/YY HH24:MI:SS'::text) AS time_preview,
    to_char(i.time_start, 'DD/MM/YY HH24:MI:SS'::text) AS time_start,
    to_char(i.time_stop, 'DD/MM/YY HH24:MI:SS'::text) AS time_stop,
    i.note_ente,
    i.geom,
    s.id AS id_segnalazione,
    s.id_lavorazione,
    i.id_evento,
    s.descrizione AS descrizione_segnalazione,
    s.criticita,
    s.rischio,
    st.id_stato_sopralluogo,
    t.descrizione AS descrizione_stato,
    st.data_ora_stato,
    st.parziale
   FROM (((((((segnalazioni.t_sopralluoghi i
     JOIN segnalazioni.join_sopralluoghi_squadra sq ON ((i.id = sq.id_sopralluogo)))
     JOIN segnalazioni.stato_sopralluoghi st ON ((st.id_sopralluogo = i.id)))
     JOIN segnalazioni.tipo_stato_sopralluoghi t ON ((t.id = st.id_stato_sopralluogo)))
     LEFT JOIN segnalazioni.join_segnalazioni_sopralluoghi j ON ((i.id = j.id_sopralluogo)))
     LEFT JOIN segnalazioni.v_segnalazioni s ON ((s.id_lavorazione = j.id_segnalazione_in_lavorazione)))
     JOIN users.v_squadre_all u ON (((u.id)::text = (sq.id_squadra)::text)))
     JOIN eventi.t_eventi e ON ((e.id = i.id_evento)))
  WHERE ((sq.valido = true) AND ((e.valido = true) OR (e.valido IS NULL)));


--
-- Name: v_sopralluoghi_eventi_chiusi; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_sopralluoghi_eventi_chiusi AS
 SELECT i.id,
    to_char(i.data_ora_invio, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_invio,
    i.id_profilo,
    i.descrizione,
    sq.id_squadra,
    u.nome AS descrizione_uo,
    to_char(i.time_preview, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_preview,
    to_char(i.time_start, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_start,
    to_char(i.time_stop, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_stop,
    i.note_ente,
    i.geom,
    s.id AS id_segnalazione,
    s.id_lavorazione,
    i.id_evento,
    s.descrizione AS descrizione_segnalazione,
    s.criticita,
    s.rischio,
    st.id_stato_sopralluogo,
    t.descrizione AS descrizione_stato,
    st.data_ora_stato,
    st.parziale
   FROM (((((((segnalazioni.t_sopralluoghi i
     JOIN segnalazioni.join_sopralluoghi_squadra sq ON ((i.id = sq.id_sopralluogo)))
     LEFT JOIN segnalazioni.join_segnalazioni_sopralluoghi j ON ((j.id_sopralluogo = i.id)))
     JOIN segnalazioni.stato_sopralluoghi st ON ((st.id_sopralluogo = i.id)))
     JOIN segnalazioni.tipo_stato_sopralluoghi t ON ((t.id = st.id_stato_sopralluogo)))
     LEFT JOIN segnalazioni.v_segnalazioni_eventi_chiusi_lista s ON ((s.id_lavorazione = j.id_segnalazione_in_lavorazione)))
     JOIN users.v_squadre_all u ON (((u.id)::text = (sq.id_squadra)::text)))
     JOIN eventi.t_eventi e ON ((e.id = i.id_evento)))
  WHERE ((sq.valido = true) AND (e.valido = false));


--
-- Name: v_sopralluoghi_eventi_chiusi_last_update; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_sopralluoghi_eventi_chiusi_last_update AS
 SELECT i.id,
    i.data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_squadra,
    i.descrizione_uo,
    i.time_preview,
    i.time_start,
    i.time_stop,
    i.note_ente,
    i.geom,
    i.id_segnalazione,
    i.id_evento,
    i.id_lavorazione,
    i.descrizione_segnalazione,
    i.criticita,
    i.rischio,
    i.id_stato_sopralluogo,
    i.descrizione_stato,
    i.data_ora_stato,
    i.parziale
   FROM segnalazioni.v_sopralluoghi_eventi_chiusi i
  WHERE (i.data_ora_stato = ( SELECT max(v_sopralluoghi_eventi_chiusi.data_ora_stato) AS max
           FROM segnalazioni.v_sopralluoghi_eventi_chiusi
          WHERE (v_sopralluoghi_eventi_chiusi.id = i.id)))
  ORDER BY i.id;


--
-- Name: v_sopralluoghi_last_update; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_sopralluoghi_last_update AS
 SELECT i.id,
    i.data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_squadra,
    i.descrizione_uo,
    i.time_preview,
    i.time_start,
    i.time_stop,
    i.note_ente,
    i.geom,
    i.id_segnalazione,
    i.id_evento,
    i.id_lavorazione,
    i.descrizione_segnalazione,
    i.criticita,
    i.rischio,
    i.id_stato_sopralluogo,
    i.descrizione_stato,
    i.data_ora_stato,
    i.parziale
   FROM segnalazioni.v_sopralluoghi i
  WHERE (i.data_ora_stato = ( SELECT max(v_sopralluoghi.data_ora_stato) AS max
           FROM segnalazioni.v_sopralluoghi
          WHERE (v_sopralluoghi.id = i.id)))
  ORDER BY i.id;


--
-- Name: v_comunicazioni; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_comunicazioni AS
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Centrale'::character varying AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi c
     JOIN segnalazioni.v_incarichi_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Centrale'::character varying AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi c
     JOIN segnalazioni.v_incarichi_eventi_chiusi_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Centrale'::character varying AS mittente,
    i.descrizione_uo AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_inviate c
     JOIN segnalazioni.v_incarichi_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Centrale'::character varying AS mittente,
    i.descrizione_uo AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_inviate c
     JOIN segnalazioni.v_incarichi_eventi_chiusi_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_interni c
     JOIN segnalazioni.v_incarichi_interni_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_interni c
     JOIN segnalazioni.v_incarichi_interni_eventi_chiusi_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Responsabile segnalazione'::character varying AS mittente,
    i.descrizione_uo AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_interni_inviate c
     JOIN segnalazioni.v_incarichi_interni_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Responsabile segnalazione'::character varying AS mittente,
    i.descrizione_uo AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_interni_inviate c
     JOIN segnalazioni.v_incarichi_interni_eventi_chiusi_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    ((u.nome)::text || '(responsabile sopralluogo)'::text) AS mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi c
     JOIN segnalazioni.join_sopralluoghi_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Responsabile segnalazione'::character varying AS mittente,
    ((u.nome)::text || '(responsabile sopralluogo)'::text) AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi_inviate c
     JOIN segnalazioni.join_sopralluoghi_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    ((u.nome)::text || '(responsabile sopralluogo)'::text) AS mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi c
     JOIN segnalazioni.join_sopralluoghi_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_eventi_chiusi_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Responsabile segnalazione'::character varying AS mittente,
    ((u.nome)::text || '(responsabile sopralluogo)'::text) AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi_inviate c
     JOIN segnalazioni.join_sopralluoghi_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_eventi_chiusi_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Centrale'::character varying AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_provvedimenti_cautelari c
     JOIN segnalazioni.v_provvedimenti_cautelari_last_update i ON ((i.id = c.id_provvedimento)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Centrale'::character varying AS destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_provvedimenti_cautelari c
     JOIN segnalazioni.v_provvedimenti_cautelari_eventi_chiusi_last_update i ON ((i.id = c.id_provvedimento)))
UNION
 SELECT 0 AS id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    c.mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    c.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_segnalazioni c
     JOIN segnalazioni.v_segnalazioni i ON ((i.id_lavorazione = c.id_lavorazione)))
UNION
 SELECT 0 AS id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    c.mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    c.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_segnalazioni c
     JOIN segnalazioni.v_segnalazioni_eventi_chiusi_lista i ON ((i.id_lavorazione = c.id_lavorazione)))
  ORDER BY 3;


--
-- Name: v_comunicazioni_aperte; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_comunicazioni_aperte AS
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Centrale'::character varying AS destinatario,
    i.id_profilo AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi c
     JOIN segnalazioni.v_incarichi_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YY HH24:MI:SS'::text) AS data_ora_stato,
    'Centrale'::character varying AS mittente,
    i.descrizione_uo AS destinatario,
    i.id_uo AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_inviate c
     JOIN segnalazioni.v_incarichi_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    i.id_profilo AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_interni c
     JOIN segnalazioni.v_incarichi_interni_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Responsabile segnalazione'::character varying AS mittente,
    i.descrizione_uo AS destinatario,
    ('sq_'::text || (i.id_squadra)::text) AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_interni_inviate c
     JOIN segnalazioni.v_incarichi_interni_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    ((u.nome)::text || '(responsabile sopralluogo)'::text) AS mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    i.id_profilo AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi c
     JOIN segnalazioni.join_sopralluoghi_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Responsabile segnalazione'::character varying AS mittente,
    ((u.nome)::text || '(responsabile sopralluogo)'::text) AS destinatario,
    ('sq_'::text || i.id_squadra) AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi_inviate c
     JOIN segnalazioni.join_sopralluoghi_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Centrale'::character varying AS destinatario,
    i.id_profilo AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_provvedimenti_cautelari c
     JOIN segnalazioni.v_provvedimenti_cautelari_last_update i ON ((i.id = c.id_provvedimento)))
UNION
 SELECT 0 AS id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    c.mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    (i.id_profilo)::text AS id_destinatario,
    c.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_segnalazioni c
     JOIN segnalazioni.v_segnalazioni i ON ((i.id_lavorazione = c.id_lavorazione)))
  ORDER BY 3;


--
-- Name: v_comunicazioni_incarichi; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_comunicazioni_incarichi AS
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Centrale'::character varying AS destinatario,
    (i.id_profilo)::text AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi c
     JOIN segnalazioni.v_incarichi_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Centrale'::character varying AS mittente,
    i.descrizione_uo AS destinatario,
    (i.id_uo)::text AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_inviate c
     JOIN segnalazioni.v_incarichi_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Centrale'::character varying AS destinatario,
    (i.id_profilo)::text AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi c
     JOIN segnalazioni.v_incarichi_eventi_chiusi_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Centrale'::character varying AS mittente,
    i.descrizione_uo AS destinatario,
    (i.id_uo)::text AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_inviate c
     JOIN segnalazioni.v_incarichi_eventi_chiusi_last_update i ON ((i.id = c.id_incarico)));


--
-- Name: v_comunicazioni_incarichi_interni; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_comunicazioni_incarichi_interni AS
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    (i.id_profilo)::text AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_interni c
     JOIN segnalazioni.v_incarichi_interni_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Responsabile segnalazione'::character varying AS mittente,
    i.descrizione_uo AS destinatario,
    ('sq_'::text || (i.id_squadra)::text) AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_interni_inviate c
     JOIN segnalazioni.v_incarichi_interni_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    (i.id_profilo)::text AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_interni c
     JOIN segnalazioni.v_incarichi_interni_eventi_chiusi_last_update i ON ((i.id = c.id_incarico)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Responsabile segnalazione'::character varying AS mittente,
    i.descrizione_uo AS destinatario,
    ('sq_'::text || (i.id_squadra)::text) AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_incarichi_interni_inviate c
     JOIN segnalazioni.v_incarichi_interni_eventi_chiusi_last_update i ON ((i.id = c.id_incarico)))
  ORDER BY 3;


--
-- Name: v_comunicazioni_provvedimenti_cautelari; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_comunicazioni_provvedimenti_cautelari AS
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Centrale'::character varying AS destinatario,
    (i.id_profilo)::text AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_provvedimenti_cautelari c
     JOIN segnalazioni.v_provvedimenti_cautelari_last_update i ON ((i.id = c.id_provvedimento)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Centrale'::character varying AS mittente,
    i.descrizione_uo AS destinatario,
    (i.id_squadra)::text AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_provvedimenti_cautelari_inviate c
     JOIN segnalazioni.v_provvedimenti_cautelari_last_update i ON ((i.id = c.id_provvedimento)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    i.descrizione_uo AS mittente,
    'Centrale'::character varying AS destinatario,
    (i.id_profilo)::text AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_provvedimenti_cautelari c
     JOIN segnalazioni.v_provvedimenti_cautelari_eventi_chiusi_last_update i ON ((i.id = c.id_provvedimento)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Centrale'::character varying AS mittente,
    i.descrizione_uo AS destinatario,
    (i.id_squadra)::text AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (segnalazioni.t_comunicazioni_provvedimenti_cautelari_inviate c
     JOIN segnalazioni.v_provvedimenti_cautelari_eventi_chiusi_last_update i ON ((i.id = c.id_provvedimento)));


--
-- Name: v_comunicazioni_sopralluoghi; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_comunicazioni_sopralluoghi AS
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    u.nome AS mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    (i.id_profilo)::text AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi c
     JOIN segnalazioni.join_sopralluoghi_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Responsabile segnalazione'::character varying AS mittente,
    u.nome AS destinatario,
    ('sq_'::text || (i.id_squadra)::text) AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi_inviate c
     JOIN segnalazioni.join_sopralluoghi_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    u.nome AS mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    (i.id_profilo)::text AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi c
     JOIN segnalazioni.join_sopralluoghi_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_eventi_chiusi_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Responsabile segnalazione'::character varying AS mittente,
    u.nome AS destinatario,
    ('sq_'::text || (i.id_squadra)::text) AS id_destinatario,
    i.id_lavorazione,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi_inviate c
     JOIN segnalazioni.join_sopralluoghi_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_eventi_chiusi_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
  ORDER BY 3;


--
-- Name: v_sopralluoghi_mobili; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_sopralluoghi_mobili AS
 SELECT i.id,
    to_char(i.data_ora_invio, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_invio,
    i.id_profilo,
    i.descrizione,
    sq.id_squadra,
    u.nome AS descrizione_uo,
    to_char(i.time_preview, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_preview,
    to_char(i.time_start, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_start,
    to_char(i.time_stop, 'DD/MM/YY HH24:MI:SS'::text) AS time_stop,
    i.note_ente,
    i.geom,
    i.id_evento,
    st.id_stato_sopralluogo,
    t.descrizione AS descrizione_stato,
    st.data_ora_stato,
    st.parziale
   FROM (((((segnalazioni.t_sopralluoghi_mobili i
     JOIN segnalazioni.join_sopralluoghi_mobili_squadra sq ON ((i.id = sq.id_sopralluogo)))
     JOIN segnalazioni.stato_sopralluoghi_mobili st ON ((st.id_sopralluogo = i.id)))
     JOIN segnalazioni.tipo_stato_sopralluoghi t ON ((t.id = st.id_stato_sopralluogo)))
     JOIN users.v_squadre_all u ON (((u.id)::text = (sq.id_squadra)::text)))
     JOIN eventi.t_eventi e ON ((e.id = i.id_evento)))
  WHERE ((sq.valido = true) AND ((e.valido = true) OR (e.valido IS NULL)));


--
-- Name: v_sopralluoghi_mobili_eventi_chiusi; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_sopralluoghi_mobili_eventi_chiusi AS
 SELECT i.id,
    to_char(i.data_ora_invio, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_invio,
    i.id_profilo,
    i.descrizione,
    sq.id_squadra,
    u.nome AS descrizione_uo,
    to_char(i.time_preview, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_preview,
    to_char(i.time_start, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_start,
    to_char(i.time_stop, 'DD/MM/YYYY HH24:MI:SS'::text) AS time_stop,
    i.note_ente,
    i.geom,
    i.id_evento,
    st.id_stato_sopralluogo,
    t.descrizione AS descrizione_stato,
    st.data_ora_stato,
    st.parziale
   FROM (((((segnalazioni.t_sopralluoghi_mobili i
     JOIN segnalazioni.join_sopralluoghi_mobili_squadra sq ON ((i.id = sq.id_sopralluogo)))
     JOIN segnalazioni.stato_sopralluoghi_mobili st ON ((st.id_sopralluogo = i.id)))
     JOIN segnalazioni.tipo_stato_sopralluoghi t ON ((t.id = st.id_stato_sopralluogo)))
     JOIN users.v_squadre_all u ON (((u.id)::text = (sq.id_squadra)::text)))
     JOIN eventi.t_eventi e ON ((e.id = i.id_evento)))
  WHERE ((sq.valido = true) AND (e.valido = false));


--
-- Name: v_sopralluoghi_mobili_eventi_chiusi_last_update; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_sopralluoghi_mobili_eventi_chiusi_last_update AS
 SELECT i.id,
    i.data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_squadra,
    i.descrizione_uo,
    i.time_preview,
    i.time_start,
    i.time_stop,
    i.note_ente,
    i.geom,
    i.id_evento,
    i.id_stato_sopralluogo,
    i.descrizione_stato,
    i.data_ora_stato,
    i.parziale
   FROM segnalazioni.v_sopralluoghi_mobili_eventi_chiusi i
  WHERE (i.data_ora_stato = ( SELECT max(v_sopralluoghi_mobili_eventi_chiusi.data_ora_stato) AS max
           FROM segnalazioni.v_sopralluoghi_mobili_eventi_chiusi
          WHERE (v_sopralluoghi_mobili_eventi_chiusi.id = i.id)))
  ORDER BY i.id;


--
-- Name: v_sopralluoghi_mobili_last_update; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_sopralluoghi_mobili_last_update AS
 SELECT i.id,
    i.data_ora_invio,
    i.id_profilo,
    i.descrizione,
    i.id_squadra,
    i.descrizione_uo,
    i.time_preview,
    i.time_start,
    i.time_stop,
    i.note_ente,
    i.geom,
    i.id_evento,
    i.id_stato_sopralluogo,
    i.descrizione_stato,
    i.data_ora_stato,
    i.parziale
   FROM segnalazioni.v_sopralluoghi_mobili i
  WHERE (i.data_ora_stato = ( SELECT max(v_sopralluoghi_mobili.data_ora_stato) AS max
           FROM segnalazioni.v_sopralluoghi_mobili
          WHERE (v_sopralluoghi_mobili.id = i.id)))
  ORDER BY i.id;


--
-- Name: v_comunicazioni_sopralluoghi_mobili; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_comunicazioni_sopralluoghi_mobili AS
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    u.nome AS mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    (i.id_profilo)::text AS id_destinatario,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi_mobili c
     JOIN segnalazioni.join_sopralluoghi_mobili_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_mobili_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Responsabile segnalazione'::character varying AS mittente,
    u.nome AS destinatario,
    ('sq_'::text || (i.id_squadra)::text) AS id_destinatario,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi_mobili_inviate c
     JOIN segnalazioni.join_sopralluoghi_mobili_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_mobili_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    u.nome AS mittente,
    'Responsabile segnalazione'::character varying AS destinatario,
    (i.id_profilo)::text AS id_destinatario,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi_mobili c
     JOIN segnalazioni.join_sopralluoghi_mobili_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_mobili_eventi_chiusi_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
UNION
 SELECT i.id,
    c.testo,
    to_char(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) AS data_ora_stato,
    'Responsabile segnalazione'::character varying AS mittente,
    ('sq_'::text || (i.id_squadra)::text) AS destinatario,
    u.nome AS id_destinatario,
    i.id_evento,
    c.allegato
   FROM (((segnalazioni.t_comunicazioni_sopralluoghi_mobili_inviate c
     JOIN segnalazioni.join_sopralluoghi_mobili_squadra s ON ((s.id_sopralluogo = c.id_sopralluogo)))
     JOIN users.t_squadre u ON ((u.id = s.id_squadra)))
     JOIN segnalazioni.v_sopralluoghi_mobili_eventi_chiusi_last_update i ON ((i.id = c.id_sopralluogo)))
  WHERE ((c.data_ora_stato > s.data_ora) AND ((c.data_ora_stato < s.data_ora_cambio) OR (s.valido = true)))
  ORDER BY 3;


--
-- Name: v_segnalazioni_all; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_segnalazioni_all AS
 SELECT s.id,
    to_char(s.data_ora, 'YYYY/MM/DD HH24:MI'::text) AS data_ora,
    s.id_segnalante,
    s.descrizione,
    s.id_criticita,
    c.descrizione AS criticita,
    s.rischio,
    s.id_evento,
        CASE
            WHEN (e.valido = true) THEN 'Evento attivo'::text
            ELSE 'Evento in chiusura'::text
        END AS tipo_evento,
    s.id_civico,
    s.geom,
    s.id_municipio,
    s.id_operatore,
    s.note,
    jl.id_segnalazione_in_lavorazione AS id_lavorazione,
    l.in_lavorazione,
    l.id_profilo,
    l.descrizione_chiusura,
    s.uo_ins
   FROM ((((segnalazioni.t_segnalazioni s
     JOIN segnalazioni.tipo_criticita c ON ((c.id = s.id_criticita)))
     JOIN eventi.t_eventi e ON ((e.id = s.id_evento)))
     LEFT JOIN segnalazioni.join_segnalazioni_in_lavorazione jl ON ((jl.id_segnalazione = s.id)))
     LEFT JOIN segnalazioni.t_segnalazioni_in_lavorazione l ON ((jl.id_segnalazione_in_lavorazione = l.id)))
  ORDER BY s.id_evento, l.in_lavorazione DESC;


--
-- Name: v_count_risolte; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_count_risolte AS
 SELECT count(s.id) AS risolte,
    s.criticita,
    s.id_evento
   FROM segnalazioni.v_segnalazioni_all s
  WHERE (s.in_lavorazione = false)
  GROUP BY s.criticita, s.id_evento;


--
-- Name: v_incarichi_squadre_temp; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_incarichi_squadre_temp AS
 SELECT i.id,
    'incarico_interno'::text AS descrizione,
    (i.id_squadra)::text AS id_squadra,
    i.descrizione_uo,
    i.data_ora_stato
   FROM segnalazioni.v_incarichi_interni_last_update i
  WHERE (i.data_ora_stato = ( SELECT max(v_incarichi_interni_last_update.data_ora_stato) AS max
           FROM segnalazioni.v_incarichi_interni_last_update
          WHERE ((v_incarichi_interni_last_update.id_squadra)::text = (i.id_squadra)::text)
          GROUP BY v_incarichi_interni_last_update.id_squadra))
UNION
 SELECT s.id,
    'sopralluogo'::text AS descrizione,
    (s.id_squadra)::text AS id_squadra,
    s.descrizione_uo,
    s.data_ora_stato
   FROM segnalazioni.v_sopralluoghi_last_update s
  WHERE (s.data_ora_stato = ( SELECT max(v_sopralluoghi_last_update.data_ora_stato) AS max
           FROM segnalazioni.v_sopralluoghi_last_update
          WHERE (v_sopralluoghi_last_update.id_squadra = s.id_squadra)
          GROUP BY v_sopralluoghi_last_update.id_squadra))
UNION
 SELECT sm.id,
    'sopralluogo_mobile'::text AS descrizione,
    (sm.id_squadra)::text AS id_squadra,
    sm.descrizione_uo,
    sm.data_ora_stato
   FROM segnalazioni.v_sopralluoghi_mobili_last_update sm
  WHERE (sm.data_ora_stato = ( SELECT max(v_sopralluoghi_mobili_last_update.data_ora_stato) AS max
           FROM segnalazioni.v_sopralluoghi_mobili_last_update
          WHERE (v_sopralluoghi_mobili_last_update.id_squadra = sm.id_squadra)
          GROUP BY v_sopralluoghi_mobili_last_update.id_squadra))
  ORDER BY 3;


--
-- Name: v_incarichi_squadre; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_incarichi_squadre AS
 SELECT i.id,
    i.descrizione,
    i.id_squadra,
    i.descrizione_uo,
    i.data_ora_stato
   FROM segnalazioni.v_incarichi_squadre_temp i
  WHERE (i.data_ora_stato = ( SELECT max(v_incarichi_squadre_temp.data_ora_stato) AS max
           FROM segnalazioni.v_incarichi_squadre_temp
          WHERE (v_incarichi_squadre_temp.id_squadra = i.id_squadra)
          GROUP BY v_incarichi_squadre_temp.id_squadra));


--
-- Name: v_join_oggetto_rischio; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_join_oggetto_rischio AS
 SELECT s.id_segnalazione,
    s.id_tipo_oggetto,
    s.id_oggetto,
    s.attivo,
    l.id_segnalazione_in_lavorazione
   FROM (segnalazioni.join_oggetto_rischio s
     LEFT JOIN segnalazioni.join_segnalazioni_in_lavorazione l ON ((s.id_segnalazione = l.id_segnalazione)))
  WHERE (s.attivo = true);


--
-- Name: v_residenti_allontanati; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_residenti_allontanati AS
 SELECT p.tipo_provvedimento,
    p.descrizione_stato,
    p.id_oggetto,
    p.tipo_oggetto,
    p.id_evento,
        CASE
            WHEN ((p.tipo_oggetto)::text = 'geodb.edifici'::text) THEN ( SELECT count(anagrafe_edifici.codfisc) AS count
               FROM geodb.anagrafe_edifici
              WHERE (anagrafe_edifici.id_edificio = p.id_oggetto))
            WHEN ((p.tipo_oggetto)::text = 'geodb.civici'::text) THEN ( SELECT count(anagrafe_civici.codfisc) AS count
               FROM geodb.anagrafe_civici
              WHERE (anagrafe_civici.id_civico = p.id_oggetto))
            ELSE NULL::bigint
        END AS residenti
   FROM segnalazioni.v_provvedimenti_cautelari_last_update p
  WHERE ((p.rimosso = false) AND (p.id_tipo_pc = 1) AND (p.id_stato_provvedimenti_cautelari = 3));


--
-- Name: v_segnalanti; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_segnalanti AS
 SELECT ss.id AS id_segnalazione,
    s.id AS id_segnalante,
    s.id_tipo_segnalante,
    t.descrizione,
    s.altro_tipo,
    s.nome_cognome,
    s.telefono,
    s.note
   FROM segnalazioni.t_segnalanti s,
    segnalazioni.t_segnalazioni ss,
    segnalazioni.tipo_segnalanti t
  WHERE ((t.id = s.id_tipo_segnalante) AND (ss.id_segnalante = s.id));


--
-- Name: v_segnalazioni_eventi_chiusi; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_segnalazioni_eventi_chiusi AS
 SELECT s.id,
    to_char(s.data_ora, 'DD/MM/YY HH24:MM:SS'::text) AS data_ora,
    s.id_segnalante,
    s.descrizione,
    s.id_criticita,
    c.descrizione AS criticita,
    s.rischio,
    s.id_evento,
    s.id_civico,
    s.geom,
    s.id_municipio,
    s.id_operatore,
    s.note,
        CASE
            WHEN (( SELECT join_segnalazioni_in_lavorazione.id_segnalazione_in_lavorazione
               FROM segnalazioni.join_segnalazioni_in_lavorazione
              WHERE (join_segnalazioni_in_lavorazione.id_segnalazione = s.id)) > 0) THEN 1
            ELSE 0
        END AS lavorazione
   FROM segnalazioni.t_segnalazioni s,
    segnalazioni.tipo_criticita c,
    eventi.t_eventi e
  WHERE ((c.id = s.id_criticita) AND (e.id = s.id_evento) AND (e.valido = false));


--
-- Name: v_segnalazioni_lista; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_segnalazioni_lista AS
 SELECT s.id,
    to_char(s.data_ora, 'YYYY/MM/DD HH24:MI'::text) AS data_ora,
    s.id_segnalante,
    s.descrizione,
    s.id_criticita,
    c.descrizione AS criticita,
    s.rischio,
    s.id_evento,
        CASE
            WHEN (e.valido = true) THEN 'Evento attivo'::text
            ELSE 'Evento in chiusura'::text
        END AS tipo_evento,
    s.id_civico,
        CASE
            WHEN (s.id_civico IS NULL) THEN ( SELECT concat('~ ', civici.desvia, ' ', civici.testo) AS concat
               FROM geodb.civici
              WHERE (civici.geom OPERATOR(public.&&) public.st_expand(public.st_transform(s.geom, 3003), (250)::double precision))
              ORDER BY (public.st_distance(civici.geom, public.st_transform(s.geom, 3003)))
             LIMIT 1)
            ELSE (((g.desvia)::text || ' '::text) || (g.testo)::text)
        END AS localizzazione,
    s.geom,
    s.id_municipio,
    m.nome_munic,
    s.id_operatore,
    s.note,
    jl.id_segnalazione_in_lavorazione AS id_lavorazione,
    l.in_lavorazione,
        CASE
            WHEN (l.id_profilo = 5) THEN (((l.id_profilo)::text || '_'::text) || (s.id_municipio)::text)
            ELSE (l.id_profilo)::text
        END AS id_profilo,
    e.fine_sospensione,
    l.id_man
   FROM ((((((segnalazioni.t_segnalazioni s
     JOIN segnalazioni.tipo_criticita c ON ((c.id = s.id_criticita)))
     JOIN eventi.t_eventi e ON ((e.id = s.id_evento)))
     LEFT JOIN segnalazioni.join_segnalazioni_in_lavorazione jl ON ((jl.id_segnalazione = s.id)))
     LEFT JOIN segnalazioni.t_segnalazioni_in_lavorazione l ON ((jl.id_segnalazione_in_lavorazione = l.id)))
     LEFT JOIN geodb.municipi m ON ((s.id_municipio = (m.id)::integer)))
     LEFT JOIN geodb.civici g ON ((g.id = s.id_civico)))
  WHERE ((e.valido = true) OR (e.valido IS NULL))
  ORDER BY s.id_evento, l.in_lavorazione DESC;


--
-- Name: v_segnalazioni_lista_eventi_chiusi; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_segnalazioni_lista_eventi_chiusi AS
 SELECT s.id,
    to_char(s.data_ora, 'YYYY/MM/DD HH24:MM:SS'::text) AS data_ora,
    s.id_segnalante,
    s.descrizione,
    s.id_criticita,
    c.descrizione AS criticita,
    s.rischio,
    s.id_evento,
    s.id_civico,
    s.geom,
    s.id_municipio,
    s.id_operatore,
    s.note,
        CASE
            WHEN (( SELECT join_segnalazioni_in_lavorazione.id_segnalazione_in_lavorazione
               FROM segnalazioni.join_segnalazioni_in_lavorazione
              WHERE (join_segnalazioni_in_lavorazione.id_segnalazione = s.id)) > 0) THEN 1
            ELSE 0
        END AS lavorazione
   FROM segnalazioni.t_segnalazioni s,
    segnalazioni.tipo_criticita c,
    eventi.t_eventi e
  WHERE ((c.id = s.id_criticita) AND (e.id = s.id_evento) AND (e.valido = false));


--
-- Name: v_segnalazioni_lista_pp; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_segnalazioni_lista_pp AS
 SELECT max(s.id) AS id,
    count(s.id) AS num,
    string_agg((s.descrizione)::text, '<br>'::text) AS descrizione,
    array_to_string(array_agg(DISTINCT (c.descrizione)::text), '<br>'::text) AS criticita,
    array_to_string(array_agg(DISTINCT (m.nome_munic)::text), '<br>'::text) AS nome_munic,
    string_agg(
        CASE
            WHEN (s.id_civico IS NULL) THEN ( SELECT concat('~ ', civici.desvia, ' ', civici.testo) AS concat
               FROM geodb.civici
              WHERE (civici.geom OPERATOR(public.&&) public.st_expand(public.st_transform(s.geom, 3003), (250)::double precision))
              ORDER BY (public.st_distance(civici.geom, public.st_transform(s.geom, 3003)))
             LIMIT 1)
            ELSE (((g.desvia)::text || ' '::text) || (g.testo)::text)
        END, '<br>'::text) AS localizzazione,
    jl.id_segnalazione_in_lavorazione AS id_lavorazione,
    l.in_lavorazione,
    l.id_profilo,
        CASE
            WHEN ((( SELECT count(i.id) AS sum
               FROM segnalazioni.v_incarichi_last_update i
              WHERE ((i.id_lavorazione = jl.id_segnalazione_in_lavorazione) AND (i.id_stato_incarico < 3))) > 0) OR (( SELECT count(i.id) AS sum
               FROM segnalazioni.v_incarichi_interni_last_update i
              WHERE ((i.id_lavorazione = jl.id_segnalazione_in_lavorazione) AND (i.id_stato_incarico < 3))) > 0) OR (( SELECT count(i.id) AS sum
               FROM segnalazioni.v_provvedimenti_cautelari_last_update i
              WHERE ((i.id_lavorazione = jl.id_segnalazione_in_lavorazione) AND (i.id_stato_provvedimenti_cautelari < 3))) > 0) OR (( SELECT count(i.id) AS sum
               FROM segnalazioni.v_sopralluoghi_last_update i
              WHERE ((i.id_lavorazione = jl.id_segnalazione_in_lavorazione) AND (i.id_stato_sopralluogo < 3))) > 0)) THEN 't'::text
            ELSE 'f'::text
        END AS incarichi,
    s.id_evento,
    max((s.geom)::text) AS geom,
    e.fine_sospensione
   FROM ((((((segnalazioni.t_segnalazioni s
     JOIN segnalazioni.tipo_criticita c ON ((c.id = s.id_criticita)))
     JOIN eventi.t_eventi e ON ((e.id = s.id_evento)))
     LEFT JOIN segnalazioni.join_segnalazioni_in_lavorazione jl ON ((jl.id_segnalazione = s.id)))
     LEFT JOIN segnalazioni.t_segnalazioni_in_lavorazione l ON ((jl.id_segnalazione_in_lavorazione = l.id)))
     LEFT JOIN geodb.municipi m ON ((s.id_municipio = (m.id)::integer)))
     LEFT JOIN geodb.civici g ON ((g.id = s.id_civico)))
  WHERE (l.in_lavorazione = true)
  GROUP BY jl.id_segnalazione_in_lavorazione, l.in_lavorazione, l.id_profilo, s.id_evento, e.fine_sospensione
  ORDER BY l.in_lavorazione DESC;


--
-- Name: v_sopralluoghi_conteggio; Type: VIEW; Schema: segnalazioni; Owner: -
--

CREATE VIEW segnalazioni.v_sopralluoghi_conteggio AS
 SELECT v_sopralluoghi_last_update.id,
    v_sopralluoghi_last_update.id_stato_sopralluogo
   FROM segnalazioni.v_sopralluoghi_last_update
  GROUP BY v_sopralluoghi_last_update.id, v_sopralluoghi_last_update.id_stato_sopralluogo;


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
-- Name: profili_utilizzatore_id_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.profili_utilizzatore_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: profili_utilizzatore_id_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.profili_utilizzatore_id_seq OWNED BY users.profili_utilizzatore.id;


--
-- Name: stato_squadre_id_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.stato_squadre_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: stato_squadre_id_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.stato_squadre_id_seq OWNED BY users.t_stato_squadre.id;


--
-- Name: t_centrali; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.t_centrali (
    cod character varying NOT NULL,
    descrizione character varying,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: t_convocazione; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.t_convocazione (
    id integer NOT NULL,
    id_notifica numeric,
    data_invio timestamp(0) without time zone,
    data_conferma timestamp(0) without time zone,
    lettura boolean,
    id_telegram character varying,
    data_invio_conv timestamp(0) without time zone,
    data_conferma_conv timestamp(0) without time zone,
    lettura_conv boolean
);


--
-- Name: t_convocazione_id_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.t_convocazione_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_convocazione_id_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.t_convocazione_id_seq OWNED BY users.t_convocazione.id;


--
-- Name: t_mail_meteo; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.t_mail_meteo (
    id integer NOT NULL,
    descrizione character varying NOT NULL,
    mail character varying NOT NULL,
    valido boolean DEFAULT true
);


--
-- Name: t_mail_meteo_id_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.t_mail_meteo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_mail_meteo_id_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.t_mail_meteo_id_seq OWNED BY users.t_mail_meteo.id;


--
-- Name: t_mail_squadre; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.t_mail_squadre (
    cod character varying NOT NULL,
    mail character varying NOT NULL,
    matricola_cf character varying
);


--
-- Name: t_posizioni_squadre; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.t_posizioni_squadre (
    id_squadra integer NOT NULL,
    last_update timestamp without time zone DEFAULT now() NOT NULL,
    posizione public.geometry(Point,4326) NOT NULL
);


--
-- Name: t_presenze; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.t_presenze (
    operativo boolean,
    data_inizio timestamp(0) without time zone,
    durata integer,
    data_fine timestamp(0) without time zone,
    id integer NOT NULL,
    id_telegram character varying,
    data_fine_hp timestamp(0) without time zone
);


--
-- Name: t_presenze_id_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.t_presenze_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_presenze_id_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.t_presenze_id_seq OWNED BY users.t_presenze.id;


--
-- Name: t_reperibili; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.t_reperibili (
    id integer NOT NULL,
    matricola_cf character varying NOT NULL,
    data_start timestamp without time zone NOT NULL,
    data_end timestamp without time zone NOT NULL
);


--
-- Name: t_reperibili_id_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.t_reperibili_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_reperibili_id_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.t_reperibili_id_seq OWNED BY users.t_reperibili.id;


--
-- Name: t_storico_squadre; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.t_storico_squadre (
    id_squadra integer NOT NULL,
    log_aggiornamento character varying,
    data_ora timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: t_telefono_squadre; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.t_telefono_squadre (
    cod character varying NOT NULL,
    telefono character varying NOT NULL,
    matricola_cf character varying NOT NULL
);


--
-- Name: uo_1_livello_id1_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.uo_1_livello_id1_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: uo_1_livello_id1_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.uo_1_livello_id1_seq OWNED BY users.uo_1_livello.id1;


--
-- Name: uo_2_livello; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.uo_2_livello (
    id1 integer NOT NULL,
    id2 integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: uo_2_livello_id2_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.uo_2_livello_id2_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: uo_2_livello_id2_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.uo_2_livello_id2_seq OWNED BY users.uo_2_livello.id2;


--
-- Name: uo_3_livello; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.uo_3_livello (
    id1 integer NOT NULL,
    id2 integer NOT NULL,
    id3 integer NOT NULL,
    descrizione character varying NOT NULL,
    valido boolean DEFAULT true NOT NULL
);


--
-- Name: uo_3_livello_id3_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.uo_3_livello_id3_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: uo_3_livello_id3_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.uo_3_livello_id3_seq OWNED BY users.uo_3_livello.id3;


--
-- Name: utenti_coc; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.utenti_coc (
    id integer NOT NULL,
    matricola_cf character varying NOT NULL,
    nome character varying NOT NULL,
    cognome character varying NOT NULL,
    mail character varying,
    telegram_id character varying NOT NULL,
    funzione integer NOT NULL
);


--
-- Name: utenti_coc_eliminati; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.utenti_coc_eliminati (
    id integer NOT NULL,
    matricola_cf character varying NOT NULL,
    nome character varying,
    cognome character varying,
    mail character varying,
    telegram_id character varying,
    funzione integer
);


--
-- Name: utenti_coc_id_seq; Type: SEQUENCE; Schema: users; Owner: -
--

CREATE SEQUENCE users.utenti_coc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: utenti_coc_id_seq; Type: SEQUENCE OWNED BY; Schema: users; Owner: -
--

ALTER SEQUENCE users.utenti_coc_id_seq OWNED BY users.utenti_coc.id;


--
-- Name: utenti_esterni; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.utenti_esterni (
    cf character varying NOT NULL,
    nome character varying NOT NULL,
    cognome character varying NOT NULL,
    nazione_nascita character varying,
    data_nascita character varying,
    comune_residenza character varying,
    cap character varying,
    indirizzo character varying,
    telefono1 character varying,
    telefono2 character varying,
    fax character varying,
    mail character varying,
    numero_gg character varying,
    valido boolean DEFAULT true NOT NULL,
    id1 integer,
    id2 integer,
    id3 integer,
    priorita boolean,
    telegram_id character varying,
    telegram_attivo boolean DEFAULT false
);


--
-- Name: utenti_esterni_copy; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.utenti_esterni_copy (
    cf character varying NOT NULL,
    nome character varying,
    cognome character varying,
    nazione_nascita character varying,
    data_nascita character varying,
    comune_residenza character varying,
    cap character varying,
    indirizzo character varying,
    telefono1 character varying,
    telefono2 character varying,
    fax character varying,
    mail character varying,
    numero_gg character varying,
    valido boolean,
    id1 integer,
    id2 integer,
    id3 integer,
    priorita boolean,
    telegram_id character varying,
    telegram_attivo boolean
);


--
-- Name: utenti_esterni_eliminati; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.utenti_esterni_eliminati (
    cf character varying NOT NULL,
    nome character varying NOT NULL,
    cognome character varying NOT NULL,
    nazione_nascita character varying,
    data_nascita character varying,
    comune_residenza character varying,
    cap character varying,
    indirizzo character varying,
    telefono1 character varying,
    telefono2 character varying,
    fax character varying,
    mail character varying,
    numero_gg character varying,
    valido boolean DEFAULT true NOT NULL,
    id1 integer,
    id2 integer,
    id3 integer,
    priorita boolean
);


--
-- Name: utenti_input_telegram; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.utenti_input_telegram (
    matricola_cf character varying NOT NULL,
    telegram_id character varying
);


--
-- Name: utenti_message_update; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.utenti_message_update (
    matricola_cf character varying NOT NULL,
    data_ora timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: utenti_sistema; Type: TABLE; Schema: users; Owner: -
--

CREATE TABLE users.utenti_sistema (
    matricola_cf character varying NOT NULL,
    id1 integer,
    id2 integer,
    id3 integer,
    id_profilo integer NOT NULL,
    note character varying,
    valido boolean DEFAULT true NOT NULL,
    cod_municipio character varying,
    privacy boolean DEFAULT false NOT NULL,
    telegram_id character varying,
    telegram_attivo boolean DEFAULT false
);


--
-- Name: comuni_italia; Type: TABLE; Schema: varie; Owner: -
--

CREATE TABLE varie.comuni_italia (
    "Codice Regione" character varying,
    "Codice Città Metropolitana" character varying,
    "Codice Provincia" character varying,
    "Progressivo del Comune" character varying,
    "Codice Comune formato alfanumerico" character varying NOT NULL,
    "Denominazione in italiano" character varying,
    "Denominazione altra lingua" character varying,
    "Codice Ripartizione Geografica" character varying,
    "Ripartizione geografica" character varying,
    "Denominazione regione" character varying,
    "Denominazione Città metropolitana" character varying,
    "Denominazione provincia" character varying,
    "Flag Comune capoluogo di provincia" character varying,
    "Sigla automobilistica" character varying,
    "Codice Comune formato numerico" character varying,
    "Codice Comune numerico con 110 province - dal 2010 al 2016" character varying,
    "Codice Comune numerico con 107 province - dal 2006 al 2009" character varying,
    "Codice Comune numerico con 103 province - dal 1995 al 2005" character varying,
    "Codice Catastale del comune" character varying,
    "Popolazione legale 2011 - 09/10/2011" character varying,
    "Codice NUTS1 2010" character varying,
    "Codice NUTS2 2010" character varying,
    "Codice NUTS3 2010" character varying,
    "Codice NUTS1 2006" character varying,
    "Codice NUTS2 2006" character varying,
    "Codice NUTS3 2006" character varying
);


--
-- Name: v_utenti_esterni; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_utenti_esterni AS
 SELECT a.cf,
    a.nome,
    a.cognome,
    a.nazione_nascita,
    a.data_nascita,
    a.indirizzo,
    b."Denominazione in italiano" AS comune,
    b."Sigla automobilistica" AS provincia,
    a.cap,
    a.telefono1,
    a.telefono2,
    a.fax,
    a.mail,
    a.numero_gg,
    a.valido,
    a.id1,
    l.descrizione AS livello1,
    a.id2,
    ll.descrizione AS livello2,
    a.id3,
    lll.descrizione AS livello3,
        CASE
            WHEN ((u.id_profilo < 5) OR (u.id_profilo > 6)) THEN (u.id_profilo)::text
            ELSE (((u.id_profilo)::text || '_'::text) || (u.cod_municipio)::text)
        END AS id_profilo,
    u.valido AS stato_profilo
   FROM (((((users.utenti_esterni a
     LEFT JOIN users.utenti_sistema u ON (((a.cf)::text = (u.matricola_cf)::text)))
     LEFT JOIN varie.comuni_italia b ON (((a.comune_residenza)::text = (b."Codice Comune formato alfanumerico")::text)))
     LEFT JOIN users.uo_1_livello l ON ((l.id1 = a.id1)))
     LEFT JOIN users.uo_2_livello ll ON (((ll.id2 = a.id2) AND (ll.id1 = a.id1))))
     LEFT JOIN users.uo_3_livello lll ON (((lll.id3 = a.id3) AND (lll.id1 = a.id1) AND (lll.id2 = a.id2))));


--
-- Name: dipendenti; Type: TABLE; Schema: varie; Owner: -
--

CREATE TABLE varie.dipendenti (
    gid integer NOT NULL,
    ruolo character varying(12),
    descr_ruolo character varying(60),
    ci numeric(8,0) NOT NULL,
    matricola character varying(9),
    cognome character varying(240) NOT NULL,
    nome character varying(40),
    data_assunzione date,
    data_cessazione date,
    codice_liv1 character varying(15),
    descr_liv1 character varying(120),
    codice_liv2 character varying(15),
    descr_liv2 character varying(120),
    codice_liv3 character varying(15),
    descr_liv3 character varying(120),
    codice_liv4 character varying(15),
    descr_liv4 character varying(120),
    codice_liv5 character varying(15),
    descr_liv5 character varying(120),
    codice_liv6 character varying(15),
    descr_liv6 character varying(120),
    codice_liv7 character varying(15),
    descr_liv7 character varying(120),
    codice_liv8 character varying(15),
    descr_liv8 character varying(120),
    codice_fiscale character varying(16)
);


--
-- Name: v_dipendenti; Type: VIEW; Schema: varie; Owner: -
--

CREATE VIEW varie.v_dipendenti AS
 SELECT d.matricola,
    d.cognome,
    d.nome,
    concat(d.descr_liv2, ' (', d.descr_liv1, ')') AS direzione_area,
    concat(d.descr_liv3, d.descr_liv4) AS settore,
    d.descr_liv5 AS ufficio,
    u.id_profilo,
    u.valido AS stato_profilo
   FROM (varie.dipendenti d
     LEFT JOIN users.utenti_sistema u ON (((d.matricola)::text = (u.matricola_cf)::text)))
  ORDER BY d.cognome;


--
-- Name: v_personale_squadre; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_personale_squadre AS
 SELECT v_dipendenti.matricola AS matricola_cf,
    v_dipendenti.cognome,
    v_dipendenti.nome,
    v_dipendenti.direzione_area AS livello1,
    v_dipendenti.settore AS livello2,
    v_dipendenti.ufficio AS livello3
   FROM varie.v_dipendenti
UNION
 SELECT v_utenti_esterni.cf AS matricola_cf,
    v_utenti_esterni.cognome,
    v_utenti_esterni.nome,
    v_utenti_esterni.livello1,
    v_utenti_esterni.livello2,
    v_utenti_esterni.livello3
   FROM users.v_utenti_esterni;


--
-- Name: v_componenti_squadre; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_componenti_squadre AS
 SELECT s.id,
    s.nome AS nome_squadra,
    c.matricola_cf,
    c.capo_squadra,
    p.cognome,
    p.nome,
    p.livello1,
    p.livello2,
    p.livello3,
    m.mail,
    t.telefono,
    s.id AS id_squadra,
    c.data_start,
    c.data_end
   FROM (((((users.t_squadre s
     JOIN users.t_componenti_squadre c ON ((s.id = c.id_squadra)))
     JOIN users.v_personale_squadre p ON (((c.matricola_cf)::text = (p.matricola_cf)::text)))
     LEFT JOIN users.t_mail_squadre m ON ((((c.matricola_cf)::text = (m.matricola_cf)::text) AND ((m.cod)::text = (s.id)::text))))
     LEFT JOIN users.t_telefono_squadre t ON ((((c.matricola_cf)::text = (t.matricola_cf)::text) AND ((t.cod)::text = (s.id)::text))))
     LEFT JOIN eventi.v_eventi e ON ((s.id_evento = e.id)))
  WHERE ((e.valido = true) OR (e.valido IS NULL))
  GROUP BY s.id, s.nome, c.matricola_cf, c.capo_squadra, p.cognome, p.nome, p.livello1, p.livello2, p.livello3, m.mail, t.telefono, c.data_start, c.data_end;


--
-- Name: v_utenti_sistema; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_utenti_sistema AS
 SELECT u.matricola_cf,
        CASE
            WHEN ((u.id_profilo < 5) OR (u.id_profilo > 7)) THEN (u.id_profilo)::text
            ELSE (((u.id_profilo)::text || '_'::text) || (u.cod_municipio)::text)
        END AS id_profilo,
    p.descrizione,
    u.note,
    u.valido,
    u.cod_municipio,
        CASE
            WHEN ((u.id_profilo = 5) OR (u.id_profilo = 6)) THEN m.nome_munic
            WHEN (u.id_profilo = 7) THEN ( SELECT (t_incarichi_comune.descrizione)::character varying(254) AS descrizione
               FROM varie.t_incarichi_comune
              WHERE (t_incarichi_comune.profilo = concat('7_', u.id_profilo)))
            WHEN (u.id_profilo = 8) THEN ( SELECT (v_utenti_esterni.livello1)::character varying(254) AS livello1
               FROM users.v_utenti_esterni
              WHERE ((v_utenti_esterni.cf)::text = (u.matricola_cf)::text))
            ELSE ''::character varying(254)
        END AS nome_munic,
    ue.nome,
    ue.cognome,
    u.privacy,
    u.telegram_id,
    u.telegram_attivo,
    ue.id1
   FROM (((users.utenti_sistema u
     JOIN users.profili_utilizzatore p ON ((u.id_profilo = p.id)))
     LEFT JOIN geodb.municipi m ON (((u.cod_municipio)::text = (m.codice_mun)::text)))
     JOIN users.utenti_esterni ue ON (((ue.cf)::text = (u.matricola_cf)::text)))
UNION
 SELECT u.matricola_cf,
        CASE
            WHEN ((u.id_profilo < 5) OR (u.id_profilo > 7)) THEN (u.id_profilo)::text
            ELSE (((u.id_profilo)::text || '_'::text) || (u.cod_municipio)::text)
        END AS id_profilo,
    p.descrizione,
    u.note,
    u.valido,
    u.cod_municipio,
        CASE
            WHEN ((u.id_profilo = 5) OR (u.id_profilo = 6)) THEN m.nome_munic
            WHEN (u.id_profilo = 7) THEN ( SELECT (t_incarichi_comune.descrizione)::character varying(254) AS descrizione
               FROM varie.t_incarichi_comune
              WHERE (t_incarichi_comune.profilo = concat('7_', u.id_profilo)))
            WHEN (u.id_profilo = 8) THEN ( SELECT (v_utenti_esterni.livello1)::character varying(254) AS livello1
               FROM users.v_utenti_esterni
              WHERE ((v_utenti_esterni.cf)::text = (u.matricola_cf)::text))
            ELSE ''::character varying(254)
        END AS nome_munic,
    ue.nome,
    ue.cognome,
    u.privacy,
    u.telegram_id,
    u.telegram_attivo,
    NULL::integer AS id1
   FROM (((users.utenti_sistema u
     JOIN users.profili_utilizzatore p ON ((u.id_profilo = p.id)))
     LEFT JOIN geodb.municipi m ON (((u.cod_municipio)::text = (m.codice_mun)::text)))
     JOIN varie.dipendenti ue ON (((ue.matricola)::text = (u.matricola_cf)::text)));


--
-- Name: v_notifiche_telegram_incarichi; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_notifiche_telegram_incarichi AS
 SELECT
        CASE
            WHEN (v_utenti_sistema.id_profilo <> (8)::text) THEN v_utenti_sistema.id_profilo
            ELSE ((v_utenti_sistema.id_profilo || '_'::text) || v_utenti_sistema.id1)
        END AS cod,
    v_utenti_sistema.telegram_id,
    v_utenti_sistema.telegram_attivo
   FROM users.v_utenti_sistema;


--
-- Name: v_profili_utilizzatore; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_profili_utilizzatore AS
 SELECT (profili_utilizzatore.id)::text AS id,
    profili_utilizzatore.descrizione,
    profili_utilizzatore.valido
   FROM users.profili_utilizzatore
  WHERE ((profili_utilizzatore.id < 5) OR (profili_utilizzatore.id > 7))
UNION
 SELECT (((p.id)::text || '_'::text) || (m.codice_mun)::text) AS id,
    (((p.descrizione)::text || ' - '::text) || (m.nome_munic)::text) AS descrizione,
    p.valido
   FROM users.profili_utilizzatore p,
    geodb.municipi m
  WHERE (p.id = 5)
UNION
 SELECT (((p.id)::text || '_'::text) || (m.codice_mun)::text) AS id,
    ((((((p.descrizione)::text || ' - Distretto '::text) || (m.codice_mun)::text) || ' ('::text) || (m.nome_munic)::text) || ')'::text) AS descrizione,
    p.valido
   FROM users.profili_utilizzatore p,
    geodb.municipi m
  WHERE (p.id = 6)
UNION
 SELECT (((p.id)::text || '_'::text) || (m.id)::text) AS id,
    (((p.descrizione)::text || ' - '::text) || (m.descrizione)::text) AS descrizione,
    p.valido
   FROM users.profili_utilizzatore p,
    users.tipo_coc_interno m
  WHERE (p.id = 7)
  ORDER BY 1;


--
-- Name: v_squadre; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_squadre AS
SELECT
    NULL::integer AS id,
    NULL::character varying AS nome,
    NULL::text AS evento,
    NULL::integer AS id_stato,
    NULL::character varying AS stato,
    NULL::character varying AS cod_afferenza,
    NULL::character varying AS afferenza,
    NULL::bigint AS num_componenti,
    NULL::character varying AS profilo,
    NULL::text AS componenti,
    NULL::boolean AS da_nascondere;


--
-- Name: v_squadre_notifica; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_squadre_notifica AS
 SELECT s.id,
    s.nome,
    ((((e.descrizione)::text || ' ('::text) || s.id_evento) || ')'::text) AS evento,
    s.id_stato,
    ss.id AS id_incarico_interno,
    so.id AS id_sopralluogo,
    sm.id AS id_sm
   FROM (((((users.t_squadre s
     LEFT JOIN eventi.v_eventi e ON ((s.id_evento = e.id)))
     JOIN users.t_stato_squadre st ON ((s.id_stato = st.id)))
     LEFT JOIN segnalazioni.v_incarichi_interni_last_update ss ON ((((s.id)::text = (ss.id_squadra)::text) AND (ss.id_stato_incarico < 3))))
     LEFT JOIN segnalazioni.v_sopralluoghi_last_update so ON ((((s.id)::text = (so.id_squadra)::text) AND (so.id_stato_sopralluogo < 3))))
     LEFT JOIN segnalazioni.v_sopralluoghi_mobili_last_update sm ON ((((s.id)::text = (sm.id_squadra)::text) AND (sm.id_stato_sopralluogo < 3))))
  WHERE (((e.valido = true) OR (e.valido IS NULL) OR (s.id_evento IS NULL)) AND (s.da_nascondere = false) AND (s.id_stato = 1))
  GROUP BY s.id, s.nome, ((((e.descrizione)::text || ' ('::text) || s.id_evento) || ')'::text), s.id_stato, ss.id, so.id, sm.id;


--
-- Name: v_utenti_esterni_telegram; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_utenti_esterni_telegram AS
 SELECT a.cf,
    a.nome,
    a.cognome,
    a.nazione_nascita,
    a.data_nascita,
    a.indirizzo,
    b."Denominazione in italiano" AS comune,
    b."Sigla automobilistica" AS provincia,
    a.cap,
    a.telefono1,
    a.telefono2,
    a.fax,
    a.mail,
    a.numero_gg,
    a.valido,
    a.id1,
    l.descrizione AS livello1,
    a.id2,
    ll.descrizione AS livello2,
    a.id3,
    lll.descrizione AS livello3,
        CASE
            WHEN ((u.id_profilo < 5) OR (u.id_profilo > 6)) THEN (u.id_profilo)::text
            ELSE (((u.id_profilo)::text || '_'::text) || (u.cod_municipio)::text)
        END AS id_profilo,
    u.valido AS stato_profilo,
    a.telegram_id,
    a.telegram_attivo
   FROM (((((users.utenti_esterni a
     LEFT JOIN users.utenti_sistema u ON (((a.cf)::text = (u.matricola_cf)::text)))
     LEFT JOIN varie.comuni_italia b ON (((a.comune_residenza)::text = (b."Codice Comune formato alfanumerico")::text)))
     LEFT JOIN users.uo_1_livello l ON ((l.id1 = a.id1)))
     LEFT JOIN users.uo_2_livello ll ON (((ll.id2 = a.id2) AND (ll.id1 = a.id1))))
     LEFT JOIN users.uo_3_livello lll ON (((lll.id3 = a.id3) AND (lll.id1 = a.id1) AND (lll.id2 = a.id2))));


--
-- Name: v_utenti_esterni_telegram2; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_utenti_esterni_telegram2 AS
 SELECT a.cf,
    a.nome,
    a.cognome,
    a.nazione_nascita,
    a.data_nascita,
    a.indirizzo,
    b."Denominazione in italiano" AS comune,
    b."Sigla automobilistica" AS provincia,
    a.cap,
    a.telefono1,
    a.telefono2,
    a.fax,
    a.mail,
    a.numero_gg,
    a.valido,
    a.id1,
    l.descrizione AS livello1,
    a.id2,
    ll.descrizione AS livello2,
    a.id3,
    lll.descrizione AS livello3,
        CASE
            WHEN ((u.id_profilo < 5) OR (u.id_profilo > 6)) THEN (u.id_profilo)::text
            ELSE (((u.id_profilo)::text || '_'::text) || (u.cod_municipio)::text)
        END AS id_profilo,
    u.valido AS stato_profilo,
    u.telegram_id,
    u.telegram_attivo
   FROM (((((users.utenti_esterni a
     LEFT JOIN users.utenti_sistema u ON (((a.cf)::text = (u.matricola_cf)::text)))
     LEFT JOIN varie.comuni_italia b ON (((a.comune_residenza)::text = (b."Codice Comune formato alfanumerico")::text)))
     LEFT JOIN users.uo_1_livello l ON ((l.id1 = a.id1)))
     LEFT JOIN users.uo_2_livello ll ON (((ll.id2 = a.id2) AND (ll.id1 = a.id1))))
     LEFT JOIN users.uo_3_livello lll ON (((lll.id3 = a.id3) AND (lll.id1 = a.id1) AND (lll.id2 = a.id2))));


--
-- Name: v_utenti_inesistenti; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_utenti_inesistenti AS
 SELECT utenti_sistema.matricola_cf,
    utenti_sistema.id1,
    utenti_sistema.id2,
    utenti_sistema.id3,
    utenti_sistema.id_profilo,
    utenti_sistema.note,
    utenti_sistema.valido,
    utenti_sistema.cod_municipio
   FROM users.utenti_sistema
  WHERE (NOT ((utenti_sistema.matricola_cf)::text IN ( SELECT v_utenti_sistema.matricola_cf
           FROM users.v_utenti_sistema)));


--
-- Name: v_utenti_presenti; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_utenti_presenti AS
 SELECT u.matricola_cf,
    u.nome,
    u.cognome,
    u.id_profilo,
    u.descrizione,
    u.nome_munic,
    u.telegram_id,
    u.telegram_attivo,
    tp.operativo,
    tp.data_inizio,
    tp.durata,
    tp.data_fine,
    tp.id
   FROM (users.v_utenti_sistema u
     LEFT JOIN users.t_presenze tp ON (((u.telegram_id)::text = (tp.id_telegram)::text)))
  WHERE (tp.operativo = true)
  GROUP BY u.matricola_cf, u.nome, u.cognome, u.id_profilo, u.telegram_id, u.telegram_attivo, tp.operativo, tp.data_inizio, tp.durata, tp.data_fine, tp.id, u.descrizione, u.nome_munic;


--
-- Name: v_utenti_totali; Type: VIEW; Schema: users; Owner: -
--

CREATE VIEW users.v_utenti_totali AS
 SELECT u.matricola_cf,
        CASE
            WHEN ((u.id_profilo < 5) OR (u.id_profilo > 7)) THEN (u.id_profilo)::text
            ELSE (((u.id_profilo)::text || '_'::text) || (u.cod_municipio)::text)
        END AS id_profilo,
    p.descrizione,
    ue.nome,
    ue.cognome,
    u.telegram_id,
    u.telegram_attivo
   FROM ((users.utenti_sistema u
     JOIN users.profili_utilizzatore p ON ((u.id_profilo = p.id)))
     JOIN users.utenti_esterni ue ON (((ue.cf)::text = (u.matricola_cf)::text)))
UNION
 SELECT u.matricola_cf,
        CASE
            WHEN ((u.id_profilo < 5) OR (u.id_profilo > 7)) THEN (u.id_profilo)::text
            ELSE (((u.id_profilo)::text || '_'::text) || (u.cod_municipio)::text)
        END AS id_profilo,
    p.descrizione,
    ue.nome,
    ue.cognome,
    u.telegram_id,
    u.telegram_attivo
   FROM ((users.utenti_sistema u
     JOIN users.profili_utilizzatore p ON ((u.id_profilo = p.id)))
     JOIN varie.dipendenti ue ON (((ue.matricola)::text = (u.matricola_cf)::text)))
UNION
 SELECT u.cf AS matricola_cf,
    u.id_profilo,
    u.livello1 AS descrizione,
    u.nome,
    u.cognome,
    u.telegram_id,
    u.telegram_attivo
   FROM users.v_utenti_esterni_telegram u;


--
-- Name: codici_incarichi; Type: VIEW; Schema: varie; Owner: -
--

CREATE VIEW varie.codici_incarichi AS
 SELECT concat('com_', t_incarichi_comune.cod) AS cod,
    (t_incarichi_comune.profilo)::character varying AS profilo
   FROM varie.t_incarichi_comune
UNION
 SELECT concat('uo_', uo_1_livello.id1) AS cod,
    concat('8_', uo_1_livello.id1) AS profilo
   FROM users.uo_1_livello
  ORDER BY 2;


--
-- Name: dipendenti2; Type: TABLE; Schema: varie; Owner: -
--

CREATE TABLE varie.dipendenti2 (
    gid integer NOT NULL,
    ruolo character varying(12),
    descr_ruolo character varying(60),
    ci numeric(8,0) NOT NULL,
    matricola character varying(9),
    cognome character varying(240) NOT NULL,
    nome character varying(40),
    data_assunzione date,
    data_cessazione date,
    codice_liv1 character varying(15),
    descr_liv1 character varying(120),
    codice_liv2 character varying(15),
    descr_liv2 character varying(120),
    codice_liv3 character varying(15),
    descr_liv3 character varying(120),
    codice_liv4 character varying(15),
    descr_liv4 character varying(120),
    codice_liv5 character varying(15),
    descr_liv5 character varying(120),
    codice_liv6 character varying(15),
    descr_liv6 character varying(120),
    codice_liv7 character varying(15),
    descr_liv7 character varying(120),
    codice_liv8 character varying(15),
    descr_liv8 character varying(120),
    codice_fiscale character varying(16)
);


--
-- Name: dipendenti2_gid_seq; Type: SEQUENCE; Schema: varie; Owner: -
--

CREATE SEQUENCE varie.dipendenti2_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: dipendenti2_gid_seq; Type: SEQUENCE OWNED BY; Schema: varie; Owner: -
--

ALTER SEQUENCE varie.dipendenti2_gid_seq OWNED BY varie.dipendenti2.gid;


--
-- Name: dipendenti_gid_seq; Type: SEQUENCE; Schema: varie; Owner: -
--

CREATE SEQUENCE varie.dipendenti_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: dipendenti_gid_seq; Type: SEQUENCE OWNED BY; Schema: varie; Owner: -
--

ALTER SEQUENCE varie.dipendenti_gid_seq OWNED BY varie.dipendenti.gid;


--
-- Name: dipendenti_storici; Type: TABLE; Schema: varie; Owner: -
--

CREATE TABLE varie.dipendenti_storici (
    gid integer DEFAULT nextval('varie.dipendenti_gid_seq'::regclass) NOT NULL,
    ruolo character varying(12),
    descr_ruolo character varying(60),
    ci numeric(8,0) NOT NULL,
    matricola character varying(9),
    cognome character varying(240) NOT NULL,
    nome character varying(40),
    data_assunzione date,
    data_cessazione date,
    codice_liv1 character varying(15),
    descr_liv1 character varying(120),
    codice_liv2 character varying(15),
    descr_liv2 character varying(120),
    codice_liv3 character varying(15),
    descr_liv3 character varying(120),
    codice_liv4 character varying(15),
    descr_liv4 character varying(120),
    codice_liv5 character varying(15),
    descr_liv5 character varying(120),
    codice_liv6 character varying(15),
    descr_liv6 character varying(120),
    codice_liv7 character varying(15),
    descr_liv7 character varying(120),
    codice_liv8 character varying(15),
    descr_liv8 character varying(120),
    codice_fiscale character varying(16)
);


--
-- Name: incarichi_comune; Type: VIEW; Schema: varie; Owner: -
--

CREATE VIEW varie.incarichi_comune AS
 SELECT dipendenti.codice_liv5 AS cod,
    dipendenti.descr_liv5 AS descrizione
   FROM varie.dipendenti
  WHERE ((dipendenti.codice_liv5)::text ~~* 'MU%'::text)
  GROUP BY dipendenti.codice_liv5, dipendenti.descr_liv5
UNION
 SELECT dipendenti.codice_liv6 AS cod,
    dipendenti.descr_liv6 AS descrizione
   FROM varie.dipendenti
  WHERE ((dipendenti.descr_liv6)::text ~~* 'Distretto%'::text)
  GROUP BY dipendenti.codice_liv6, dipendenti.descr_liv6;


--
-- Name: organigramma; Type: VIEW; Schema: varie; Owner: -
--

CREATE VIEW varie.organigramma AS
 SELECT dipendenti.codice_liv2,
    dipendenti.descr_liv2,
    dipendenti.codice_liv3,
    dipendenti.descr_liv3,
    dipendenti.codice_liv4,
    dipendenti.descr_liv4,
    dipendenti.codice_liv5,
    dipendenti.descr_liv5,
    dipendenti.codice_liv6,
    dipendenti.descr_liv6
   FROM varie.dipendenti
  GROUP BY dipendenti.codice_liv2, dipendenti.descr_liv2, dipendenti.codice_liv3, dipendenti.descr_liv3, dipendenti.codice_liv4, dipendenti.descr_liv4, dipendenti.codice_liv5, dipendenti.descr_liv5, dipendenti.codice_liv6, dipendenti.descr_liv6;


--
-- Name: province; Type: VIEW; Schema: varie; Owner: -
--

CREATE VIEW varie.province AS
 SELECT comuni_italia."Codice Regione",
    comuni_italia."Denominazione regione",
        CASE
            WHEN ((comuni_italia."Codice Città Metropolitana")::text = '-'::text) THEN comuni_italia."Codice Provincia"
            WHEN ((comuni_italia."Codice Città Metropolitana")::text <> '-'::text) THEN comuni_italia."Codice Città Metropolitana"
            ELSE NULL::character varying
        END AS cod,
        CASE
            WHEN ((comuni_italia."Codice Città Metropolitana")::text = '-'::text) THEN comuni_italia."Denominazione provincia"
            WHEN ((comuni_italia."Codice Città Metropolitana")::text <> '-'::text) THEN comuni_italia."Denominazione Città metropolitana"
            ELSE NULL::character varying
        END AS nome
   FROM varie.comuni_italia
  GROUP BY comuni_italia."Codice Regione", comuni_italia."Denominazione regione",
        CASE
            WHEN ((comuni_italia."Codice Città Metropolitana")::text = '-'::text) THEN comuni_italia."Codice Provincia"
            WHEN ((comuni_italia."Codice Città Metropolitana")::text <> '-'::text) THEN comuni_italia."Codice Città Metropolitana"
            ELSE NULL::character varying
        END,
        CASE
            WHEN ((comuni_italia."Codice Città Metropolitana")::text = '-'::text) THEN comuni_italia."Denominazione provincia"
            WHEN ((comuni_italia."Codice Città Metropolitana")::text <> '-'::text) THEN comuni_italia."Denominazione Città metropolitana"
            ELSE NULL::character varying
        END
  ORDER BY
        CASE
            WHEN ((comuni_italia."Codice Città Metropolitana")::text = '-'::text) THEN comuni_italia."Denominazione provincia"
            WHEN ((comuni_italia."Codice Città Metropolitana")::text <> '-'::text) THEN comuni_italia."Denominazione Città metropolitana"
            ELSE NULL::character varying
        END;


--
-- Name: stati_2018; Type: TABLE; Schema: varie; Owner: -
--

CREATE TABLE varie.stati_2018 (
    cod character varying NOT NULL,
    nome character varying,
    sigla character varying,
    continente character varying
);


--
-- Name: t_incarichi_comune_bkp; Type: TABLE; Schema: varie; Owner: -
--

CREATE TABLE varie.t_incarichi_comune_bkp (
    cod character varying(15),
    descrizione character varying(120),
    profilo character varying,
    valido boolean
);


--
-- Name: t_log; Type: TABLE; Schema: varie; Owner: -
--

CREATE TABLE varie.t_log (
    id integer NOT NULL,
    date timestamp without time zone DEFAULT now() NOT NULL,
    schema character varying,
    operatore character varying,
    operazione character varying
);


--
-- Name: t_log_id_seq; Type: SEQUENCE; Schema: varie; Owner: -
--

CREATE SEQUENCE varie.t_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: t_log_id_seq; Type: SEQUENCE OWNED BY; Schema: varie; Owner: -
--

ALTER SEQUENCE varie.t_log_id_seq OWNED BY varie.t_log.id;


--
-- Name: v_afferenza_squadre; Type: VIEW; Schema: varie; Owner: -
--

CREATE VIEW varie.v_afferenza_squadre AS
 SELECT dipendenti.codice_liv5 AS cod,
    dipendenti.descr_liv5 AS descrizione
   FROM varie.dipendenti
  WHERE ((dipendenti.codice_liv5)::text ~~* 'MU%'::text)
  GROUP BY dipendenti.codice_liv5, dipendenti.descr_liv5
UNION
 SELECT dipendenti.codice_liv6 AS cod,
    dipendenti.descr_liv6 AS descrizione
   FROM varie.dipendenti
  WHERE ((dipendenti.descr_liv6)::text ~~* 'Distretto%'::text)
  GROUP BY dipendenti.codice_liv6, dipendenti.descr_liv6
UNION
 SELECT t_centrali.cod,
    t_centrali.descrizione
   FROM users.t_centrali
  WHERE (t_centrali.valido = true)
  ORDER BY 2;


--
-- Name: v_dipendenti_tutti_livelli; Type: VIEW; Schema: varie; Owner: -
--

CREATE VIEW varie.v_dipendenti_tutti_livelli AS
 SELECT dipendenti.matricola,
    dipendenti.cognome,
    dipendenti.nome,
    concat(dipendenti.descr_liv2, ' (', dipendenti.descr_liv1, ')') AS direzione_area,
    concat(dipendenti.descr_liv3, dipendenti.descr_liv4) AS settore,
    concat(dipendenti.descr_liv5, ' - ', dipendenti.descr_liv6, ' - ', dipendenti.descr_liv7, ' - ', dipendenti.descr_liv8) AS ufficio
   FROM varie.dipendenti
  ORDER BY dipendenti.cognome;


--
-- Name: v_incarichi_mail; Type: VIEW; Schema: varie; Owner: -
--

CREATE VIEW varie.v_incarichi_mail AS
 SELECT i.cod,
    i.descrizione,
    i.tipo,
    string_agg((m.mail)::text, ' - '::text) AS mails,
    i.profilo,
    string_agg((m.id_telegram)::text, ' - '::text) AS ids_telegram
   FROM (varie.v_incarichi_mail_input i
     LEFT JOIN users.t_mail_incarichi m ON ((i.cod = (m.cod)::text)))
  GROUP BY i.cod, i.descrizione, i.tipo, i.profilo;


--
-- Name: t_note_eventi id; Type: DEFAULT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.t_note_eventi ALTER COLUMN id SET DEFAULT nextval('eventi.t_note_eventi_id_seq'::regclass);


--
-- Name: anagrafe gid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.anagrafe ALTER COLUMN gid SET DEFAULT nextval('geodb.anagrafe_gid_seq'::regclass);


--
-- Name: aree_verdi gid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.aree_verdi ALTER COLUMN gid SET DEFAULT nextval('geodb.aree_verdi_gid_seq'::regclass);


--
-- Name: civici gid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.civici ALTER COLUMN gid SET DEFAULT nextval('geodb.civici_gid_seq'::regclass);


--
-- Name: civici_wfs ogc_fid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.civici_wfs ALTER COLUMN ogc_fid SET DEFAULT nextval('geodb.civici_ogc_fid_seq'::regclass);


--
-- Name: edifici gid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.edifici ALTER COLUMN gid SET DEFAULT nextval('geodb.edifici_gid_seq'::regclass);


--
-- Name: elemento_stradale gid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.elemento_stradale ALTER COLUMN gid SET DEFAULT nextval('geodb.elemento_stradale_gid_seq'::regclass);


--
-- Name: fiumi gid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.fiumi ALTER COLUMN gid SET DEFAULT nextval('geodb.fiumi_gid_seq'::regclass);


--
-- Name: m_tables id; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.m_tables ALTER COLUMN id SET DEFAULT nextval('geodb.m_tables_id_seq'::regclass);


--
-- Name: presidi gid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.presidi ALTER COLUMN gid SET DEFAULT nextval('geodb.presidi_gid_seq'::regclass);


--
-- Name: punti_monitoraggio gid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.punti_monitoraggio ALTER COLUMN gid SET DEFAULT nextval('geodb.punti_monitoraggio_gid_seq'::regclass);


--
-- Name: punti_monitoraggio_ok gid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.punti_monitoraggio_ok ALTER COLUMN gid SET DEFAULT nextval('geodb.punti_monitoraggio_ok_gid_seq1'::regclass);


--
-- Name: punti_monitoraggio_ok_old gid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.punti_monitoraggio_ok_old ALTER COLUMN gid SET DEFAULT nextval('geodb.punti_monitoraggio_ok_gid_seq'::regclass);


--
-- Name: rir_impianti gid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.rir_impianti ALTER COLUMN gid SET DEFAULT nextval('geodb.rir_impianti_gid_seq'::regclass);


--
-- Name: sottopassi gid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.sottopassi ALTER COLUMN gid SET DEFAULT nextval('geodb.sottopassi_gid_seq'::regclass);


--
-- Name: tracciato_stradale gid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.tracciato_stradale ALTER COLUMN gid SET DEFAULT nextval('geodb.tracciato_stradale_gid_seq'::regclass);


--
-- Name: vie_wfs ogc_fid; Type: DEFAULT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.vie_wfs ALTER COLUMN ogc_fid SET DEFAULT nextval('geodb.vie_ogc_fid_seq'::regclass);


--
-- Name: mediatore:v_civici_dbt_angolo_geoserver ogc_fid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public."mediatore:v_civici_dbt_angolo_geoserver" ALTER COLUMN ogc_fid SET DEFAULT nextval('public."mediatore:v_civici_dbt_angolo_geoserver_ogc_fid_seq"'::regclass);


--
-- Name: t_aggiornamento_meteo id; Type: DEFAULT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_aggiornamento_meteo ALTER COLUMN id SET DEFAULT nextval('report.t_aggiornamento_meteo_id_seq'::regclass);


--
-- Name: t_coordinamento id; Type: DEFAULT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_coordinamento ALTER COLUMN id SET DEFAULT nextval('report.t_coordinamento_id_seq'::regclass);


--
-- Name: t_monitoraggio_meteo id; Type: DEFAULT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_monitoraggio_meteo ALTER COLUMN id SET DEFAULT nextval('report.t_monitoraggio_meteo_id_seq'::regclass);


--
-- Name: t_operatore_anpas id; Type: DEFAULT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_operatore_anpas ALTER COLUMN id SET DEFAULT nextval('report.t_operatore_anpas_id_seq'::regclass);


--
-- Name: t_operatore_nverde id; Type: DEFAULT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_operatore_nverde ALTER COLUMN id SET DEFAULT nextval('report.t_operatore_nverde_id_seq'::regclass);


--
-- Name: t_presidio_territoriale id; Type: DEFAULT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_presidio_territoriale ALTER COLUMN id SET DEFAULT nextval('report.t_presidio_territoriale_id_seq'::regclass);


--
-- Name: t_tecnico_pc id; Type: DEFAULT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_tecnico_pc ALTER COLUMN id SET DEFAULT nextval('report.t_tecnico_pc_id_seq'::regclass);


--
-- Name: join_incarico_provvedimenti_cautelari id; Type: DEFAULT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_incarico_provvedimenti_cautelari ALTER COLUMN id SET DEFAULT nextval('segnalazioni.join_incarico_provvedimenti_cautelari_id_seq'::regclass);


--
-- Name: t_storico_mail id; Type: DEFAULT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_storico_mail ALTER COLUMN id SET DEFAULT nextval('segnalazioni.t_storico_mail_id_seq'::regclass);


--
-- Name: profili_utilizzatore id; Type: DEFAULT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.profili_utilizzatore ALTER COLUMN id SET DEFAULT nextval('users.profili_utilizzatore_id_seq'::regclass);


--
-- Name: t_convocazione id; Type: DEFAULT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_convocazione ALTER COLUMN id SET DEFAULT nextval('users.t_convocazione_id_seq'::regclass);


--
-- Name: t_mail_meteo id; Type: DEFAULT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_mail_meteo ALTER COLUMN id SET DEFAULT nextval('users.t_mail_meteo_id_seq'::regclass);


--
-- Name: t_presenze id; Type: DEFAULT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_presenze ALTER COLUMN id SET DEFAULT nextval('users.t_presenze_id_seq'::regclass);


--
-- Name: t_reperibili id; Type: DEFAULT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_reperibili ALTER COLUMN id SET DEFAULT nextval('users.t_reperibili_id_seq'::regclass);


--
-- Name: t_stato_squadre id; Type: DEFAULT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_stato_squadre ALTER COLUMN id SET DEFAULT nextval('users.stato_squadre_id_seq'::regclass);


--
-- Name: uo_1_livello id1; Type: DEFAULT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.uo_1_livello ALTER COLUMN id1 SET DEFAULT nextval('users.uo_1_livello_id1_seq'::regclass);


--
-- Name: utenti_coc id; Type: DEFAULT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.utenti_coc ALTER COLUMN id SET DEFAULT nextval('users.utenti_coc_id_seq'::regclass);


--
-- Name: dipendenti gid; Type: DEFAULT; Schema: varie; Owner: -
--

ALTER TABLE ONLY varie.dipendenti ALTER COLUMN gid SET DEFAULT nextval('varie.dipendenti_gid_seq'::regclass);


--
-- Name: dipendenti2 gid; Type: DEFAULT; Schema: varie; Owner: -
--

ALTER TABLE ONLY varie.dipendenti2 ALTER COLUMN gid SET DEFAULT nextval('varie.dipendenti2_gid_seq'::regclass);


--
-- Name: t_log id; Type: DEFAULT; Schema: varie; Owner: -
--

ALTER TABLE ONLY varie.t_log ALTER COLUMN id SET DEFAULT nextval('varie.t_log_id_seq'::regclass);


--
-- Name: t_bollettini bollettini_pkey; Type: CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.t_bollettini
    ADD CONSTRAINT bollettini_pkey PRIMARY KEY (tipo, nomefile);


--
-- Name: join_municipi join_municipi_pkey; Type: CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.join_municipi
    ADD CONSTRAINT join_municipi_pkey PRIMARY KEY (id_evento, id_municipio, data_ora_inizio);


--
-- Name: join_tipo_allerta join_tipo_allerta_pkey; Type: CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.join_tipo_allerta
    ADD CONSTRAINT join_tipo_allerta_pkey PRIMARY KEY (id_evento, id_tipo_allerta, messaggio_rlg, data_ora_inizio_allerta);


--
-- Name: join_tipo_evento join_tipo_evento_pkey; Type: CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.join_tipo_evento
    ADD CONSTRAINT join_tipo_evento_pkey PRIMARY KEY (id_evento, id_tipo_evento);


--
-- Name: join_tipo_foc join_tipo_foc_pkey; Type: CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.join_tipo_foc
    ADD CONSTRAINT join_tipo_foc_pkey PRIMARY KEY (id_evento, id_tipo_foc, data_ora_inizio_foc);


--
-- Name: t_attivazione_nverde t_attivazione_nverde_pkey; Type: CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.t_attivazione_nverde
    ADD CONSTRAINT t_attivazione_nverde_pkey PRIMARY KEY (id_evento, data_ora_inizio);


--
-- Name: t_eventi t_eventi_pkey; Type: CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.t_eventi
    ADD CONSTRAINT t_eventi_pkey PRIMARY KEY (id);


--
-- Name: t_note_eventi t_note_eventi_pkey; Type: CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.t_note_eventi
    ADD CONSTRAINT t_note_eventi_pkey PRIMARY KEY (id);


--
-- Name: anagrafe anagrafe_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.anagrafe
    ADD CONSTRAINT anagrafe_pkey PRIMARY KEY (gid);


--
-- Name: aree_verdi aree_verdi_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.aree_verdi
    ADD CONSTRAINT aree_verdi_pkey PRIMARY KEY (gid);


--
-- Name: civici_wfs civici_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.civici_wfs
    ADD CONSTRAINT civici_pkey PRIMARY KEY (ogc_fid);


--
-- Name: civici civici_pkey1; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.civici
    ADD CONSTRAINT civici_pkey1 PRIMARY KEY (gid);


--
-- Name: municipi cod_municipi_uc; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.municipi
    ADD CONSTRAINT cod_municipi_uc UNIQUE (codice_mun);


--
-- Name: edifici edifici_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.edifici
    ADD CONSTRAINT edifici_pkey PRIMARY KEY (gid);


--
-- Name: elemento_stradale elemento_stradale_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.elemento_stradale
    ADD CONSTRAINT elemento_stradale_pkey PRIMARY KEY (gid);


--
-- Name: fiumi fiumi _pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.fiumi
    ADD CONSTRAINT "fiumi
_pkey" PRIMARY KEY (gid);


--
-- Name: m_tables id_tables; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.m_tables
    ADD CONSTRAINT id_tables PRIMARY KEY (id);


--
-- Name: lettura_idrometri_arpa lettura_idrometri_arpa_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.lettura_idrometri_arpa
    ADD CONSTRAINT lettura_idrometri_arpa_pkey PRIMARY KEY (id_station, data_ora);


--
-- Name: lettura_idrometri_comune lettura_idrometri_comune_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.lettura_idrometri_comune
    ADD CONSTRAINT lettura_idrometri_comune_pkey PRIMARY KEY (id_station, data_ora);


--
-- Name: lettura_mire_modifiche lettura_mire_modifiche_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.lettura_mire_modifiche
    ADD CONSTRAINT lettura_mire_modifiche_pkey PRIMARY KEY (num_id_mira, data_ora, data_ora_mod);


--
-- Name: lettura_mire lettura_mire_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.lettura_mire
    ADD CONSTRAINT lettura_mire_pkey PRIMARY KEY (num_id_mira, data_ora);


--
-- Name: municipi municipi_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.municipi
    ADD CONSTRAINT municipi_pkey PRIMARY KEY (id);


--
-- Name: presidi presidi_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.presidi
    ADD CONSTRAINT presidi_pkey PRIMARY KEY (gid);


--
-- Name: punti_monitoraggio_ok_old punti_monitoraggio_ok_old_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.punti_monitoraggio_ok_old
    ADD CONSTRAINT punti_monitoraggio_ok_old_pkey PRIMARY KEY (gid);


--
-- Name: punti_monitoraggio_ok punti_monitoraggio_ok_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.punti_monitoraggio_ok
    ADD CONSTRAINT punti_monitoraggio_ok_pkey PRIMARY KEY (gid);


--
-- Name: punti_monitoraggio punti_monitoraggio_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.punti_monitoraggio
    ADD CONSTRAINT punti_monitoraggio_pkey PRIMARY KEY (gid);


--
-- Name: rir_impianti rir_impianti_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.rir_impianti
    ADD CONSTRAINT rir_impianti_pkey PRIMARY KEY (gid);


--
-- Name: soglie_idrometri_arpa soglie_idrometri_arpa_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.soglie_idrometri_arpa
    ADD CONSTRAINT soglie_idrometri_arpa_pkey PRIMARY KEY (cod);


--
-- Name: soglie_idrometri_comune soglie_idrometri_comune_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.soglie_idrometri_comune
    ADD CONSTRAINT soglie_idrometri_comune_pkey PRIMARY KEY (id);


--
-- Name: sottopassi sottopassi_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.sottopassi
    ADD CONSTRAINT sottopassi_pkey PRIMARY KEY (gid);


--
-- Name: tracciato_stradale tracciato_stradale_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.tracciato_stradale
    ADD CONSTRAINT tracciato_stradale_pkey PRIMARY KEY (gid);


--
-- Name: vie_wfs vie_pkey; Type: CONSTRAINT; Schema: geodb; Owner: -
--

ALTER TABLE ONLY geodb.vie_wfs
    ADD CONSTRAINT vie_pkey PRIMARY KEY (ogc_fid);


--
-- Name: mediatore:v_civici_dbt_angolo_geoserver mediatore:v_civici_dbt_angolo_geoserver_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public."mediatore:v_civici_dbt_angolo_geoserver"
    ADD CONSTRAINT "mediatore:v_civici_dbt_angolo_geoserver_pkey" PRIMARY KEY (ogc_fid);


--
-- Name: t_aggiornamento_meteo t_aggiornamento_meteo_pkey; Type: CONSTRAINT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_aggiornamento_meteo
    ADD CONSTRAINT t_aggiornamento_meteo_pkey PRIMARY KEY (id);


--
-- Name: t_comunicazione t_comunicazione_pkey; Type: CONSTRAINT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_comunicazione
    ADD CONSTRAINT t_comunicazione_pkey PRIMARY KEY (id);


--
-- Name: t_coordinamento t_coordinamento_pkey; Type: CONSTRAINT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_coordinamento
    ADD CONSTRAINT t_coordinamento_pkey PRIMARY KEY (id);


--
-- Name: t_monitoraggio_meteo t_monitoraggio_meteo_pkey; Type: CONSTRAINT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_monitoraggio_meteo
    ADD CONSTRAINT t_monitoraggio_meteo_pkey PRIMARY KEY (id);


--
-- Name: t_operatore_anpas t_operatore_anpas_pkey; Type: CONSTRAINT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_operatore_anpas
    ADD CONSTRAINT t_operatore_anpas_pkey PRIMARY KEY (id);


--
-- Name: t_operatore_nverde t_operatore_nverde_pkey; Type: CONSTRAINT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_operatore_nverde
    ADD CONSTRAINT t_operatore_nverde_pkey PRIMARY KEY (id);


--
-- Name: t_operatore_volontari t_operatore_volontari_pkey; Type: CONSTRAINT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_operatore_volontari
    ADD CONSTRAINT t_operatore_volontari_pkey PRIMARY KEY (id);


--
-- Name: t_presidio_territoriale t_presidio_territoriale_pkey; Type: CONSTRAINT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_presidio_territoriale
    ADD CONSTRAINT t_presidio_territoriale_pkey PRIMARY KEY (id);


--
-- Name: t_tecnico_pc t_tecnico_pc_pkey; Type: CONSTRAINT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_tecnico_pc
    ADD CONSTRAINT t_tecnico_pc_pkey PRIMARY KEY (id);


--
-- Name: join_oggetto_rischio join_oggetto_rischio_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_oggetto_rischio
    ADD CONSTRAINT join_oggetto_rischio_pkey PRIMARY KEY (id_segnalazione, id_tipo_oggetto, id_oggetto, attivo, aggiornamento);


--
-- Name: join_segnalazioni_in_lavorazione join_segnalazioni_in_lavorazione_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_segnalazioni_in_lavorazione
    ADD CONSTRAINT join_segnalazioni_in_lavorazione_pkey PRIMARY KEY (id_segnalazione);


--
-- Name: t_comunicazioni_incarichi pkey_t_comunicazioni_incarichi; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_comunicazioni_incarichi
    ADD CONSTRAINT pkey_t_comunicazioni_incarichi PRIMARY KEY (id_incarico, data_ora_stato);


--
-- Name: t_comunicazioni_incarichi_interni pkey_t_comunicazioni_incarichi_interni; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_comunicazioni_incarichi_interni
    ADD CONSTRAINT pkey_t_comunicazioni_incarichi_interni PRIMARY KEY (id_incarico, data_ora_stato);


--
-- Name: t_comunicazioni_incarichi_interni_inviate pkey_t_comunicazioni_incarichi_interni_inviate; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_comunicazioni_incarichi_interni_inviate
    ADD CONSTRAINT pkey_t_comunicazioni_incarichi_interni_inviate PRIMARY KEY (id_incarico, data_ora_stato);


--
-- Name: t_comunicazioni_incarichi_inviate pkey_t_comunicazioni_incarichi_inviate; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_comunicazioni_incarichi_inviate
    ADD CONSTRAINT pkey_t_comunicazioni_incarichi_inviate PRIMARY KEY (id_incarico, data_ora_stato);


--
-- Name: t_comunicazioni_provvedimenti_cautelari pkey_t_comunicazioni_provvedimenti_cautelari; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_comunicazioni_provvedimenti_cautelari
    ADD CONSTRAINT pkey_t_comunicazioni_provvedimenti_cautelari PRIMARY KEY (id_provvedimento, data_ora_stato);


--
-- Name: t_comunicazioni_provvedimenti_cautelari_inviate pkey_t_comunicazioni_provvedimenti_cautelari_inviate; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_comunicazioni_provvedimenti_cautelari_inviate
    ADD CONSTRAINT pkey_t_comunicazioni_provvedimenti_cautelari_inviate PRIMARY KEY (id_provvedimento, data_ora_stato);


--
-- Name: t_comunicazioni_segnalazioni pkey_t_comunicazioni_segnalazioni; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_comunicazioni_segnalazioni
    ADD CONSTRAINT pkey_t_comunicazioni_segnalazioni PRIMARY KEY (id_lavorazione, data_ora_stato);


--
-- Name: t_comunicazioni_segnalazioni_riservate pkey_t_comunicazioni_segnalazioni_riservate; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_comunicazioni_segnalazioni_riservate
    ADD CONSTRAINT pkey_t_comunicazioni_segnalazioni_riservate PRIMARY KEY (id_segnalazione, data_ora_stato);


--
-- Name: t_comunicazioni_sopralluoghi pkey_t_comunicazioni_sopralluoghi; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_comunicazioni_sopralluoghi
    ADD CONSTRAINT pkey_t_comunicazioni_sopralluoghi PRIMARY KEY (id_sopralluogo, data_ora_stato);


--
-- Name: t_comunicazioni_sopralluoghi_inviate pkey_t_comunicazioni_sopralluoghi_inviate; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_comunicazioni_sopralluoghi_inviate
    ADD CONSTRAINT pkey_t_comunicazioni_sopralluoghi_inviate PRIMARY KEY (id_sopralluogo, data_ora_stato);


--
-- Name: t_comunicazioni_sopralluoghi_mobili pkey_t_comunicazioni_sopralluoghi_mobili; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_comunicazioni_sopralluoghi_mobili
    ADD CONSTRAINT pkey_t_comunicazioni_sopralluoghi_mobili PRIMARY KEY (id_sopralluogo, data_ora_stato);


--
-- Name: t_comunicazioni_sopralluoghi_mobili_inviate pkey_t_comunicazioni_sopralluoghi_mobili_inviate; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_comunicazioni_sopralluoghi_mobili_inviate
    ADD CONSTRAINT pkey_t_comunicazioni_sopralluoghi_mobili_inviate PRIMARY KEY (id_sopralluogo, data_ora_stato);


--
-- Name: t_geometrie_provvedimenti_cautelari pkey_t_geometrie_provvedimenti_cautelari; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_geometrie_provvedimenti_cautelari
    ADD CONSTRAINT pkey_t_geometrie_provvedimenti_cautelari PRIMARY KEY (id_provvedimento);


--
-- Name: t_ora_rimozione_provvedimenti_cautelari pkey_t_ora_rimozione_provvedimenti_cautelari; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_ora_rimozione_provvedimenti_cautelari
    ADD CONSTRAINT pkey_t_ora_rimozione_provvedimenti_cautelari PRIMARY KEY (id_provvedimento);


--
-- Name: t_provvedimenti_cautelari pkey_t_provvedimenti_cautelari_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_provvedimenti_cautelari
    ADD CONSTRAINT pkey_t_provvedimenti_cautelari_pkey PRIMARY KEY (id);


--
-- Name: t_spostamento_segnalazioni pkey_t_spostamento_segnalazioni; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_spostamento_segnalazioni
    ADD CONSTRAINT pkey_t_spostamento_segnalazioni PRIMARY KEY (id_segnalazione, data_ora_spostamento);


--
-- Name: stato_provvedimenti_cautelari pkey_t_stato_provvedimenti_cautelari; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.stato_provvedimenti_cautelari
    ADD CONSTRAINT pkey_t_stato_provvedimenti_cautelari PRIMARY KEY (id_provvedimento, id_stato_provvedimenti_cautelari, data_ora_stato);


--
-- Name: stato_sopralluoghi pkey_t_stato_sopralluoghi; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.stato_sopralluoghi
    ADD CONSTRAINT pkey_t_stato_sopralluoghi PRIMARY KEY (id_sopralluogo, id_stato_sopralluogo, data_ora_stato);


--
-- Name: stato_sopralluoghi_mobili pkey_t_stato_sopralluoghi_mobili; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.stato_sopralluoghi_mobili
    ADD CONSTRAINT pkey_t_stato_sopralluoghi_mobili PRIMARY KEY (id_sopralluogo, id_stato_sopralluogo, data_ora_stato);


--
-- Name: t_incarichi_interni t_incarichi_interni_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_incarichi_interni
    ADD CONSTRAINT t_incarichi_interni_pkey PRIMARY KEY (id);


--
-- Name: t_incarichi_interni_richiesta_cambi t_incarichi_interni_richiesta_cambi_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_incarichi_interni_richiesta_cambi
    ADD CONSTRAINT t_incarichi_interni_richiesta_cambi_pkey PRIMARY KEY (id_incarico, data_ora);


--
-- Name: t_incarichi t_incarichi_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_incarichi
    ADD CONSTRAINT t_incarichi_pkey PRIMARY KEY (id);


--
-- Name: join_incarichi_interni_squadra t_join_incarichi_interni_squadra_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_incarichi_interni_squadra
    ADD CONSTRAINT t_join_incarichi_interni_squadra_pkey PRIMARY KEY (id_incarico, id_squadra, data_ora);


--
-- Name: join_incarico_provvedimenti_cautelari t_join_incarico_provvedimenti_cautelari; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_incarico_provvedimenti_cautelari
    ADD CONSTRAINT t_join_incarico_provvedimenti_cautelari PRIMARY KEY (id);


--
-- Name: join_segnalazioni_incarichi t_join_segnalazioni_incarichi; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_segnalazioni_incarichi
    ADD CONSTRAINT t_join_segnalazioni_incarichi PRIMARY KEY (id_incarico);


--
-- Name: join_segnalazioni_incarichi_interni t_join_segnalazioni_incarichi_interni; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_segnalazioni_incarichi_interni
    ADD CONSTRAINT t_join_segnalazioni_incarichi_interni PRIMARY KEY (id_incarico);


--
-- Name: join_segnalazioni_provvedimenti_cautelari t_join_segnalazioni_provvedimenti_cautelari; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_segnalazioni_provvedimenti_cautelari
    ADD CONSTRAINT t_join_segnalazioni_provvedimenti_cautelari PRIMARY KEY (id_provvedimento);


--
-- Name: join_segnalazioni_sopralluoghi t_join_segnalazioni_sopralluoghi; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_segnalazioni_sopralluoghi
    ADD CONSTRAINT t_join_segnalazioni_sopralluoghi PRIMARY KEY (id_sopralluogo);


--
-- Name: join_sopralluoghi_mobili_squadra t_join_sopralluoghi_mobili_squadra_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_sopralluoghi_mobili_squadra
    ADD CONSTRAINT t_join_sopralluoghi_mobili_squadra_pkey PRIMARY KEY (id_sopralluogo, id_squadra, data_ora);


--
-- Name: join_sopralluoghi_squadra t_join_sopralluoghi_squadra_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_sopralluoghi_squadra
    ADD CONSTRAINT t_join_sopralluoghi_squadra_pkey PRIMARY KEY (id_sopralluogo, id_squadra, data_ora);


--
-- Name: t_richieste_nverde t_richieste_nverde_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_richieste_nverde
    ADD CONSTRAINT t_richieste_nverde_pkey PRIMARY KEY (id);


--
-- Name: t_segnalanti t_segnalanti_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_segnalanti
    ADD CONSTRAINT t_segnalanti_pkey PRIMARY KEY (id);


--
-- Name: t_segnalazioni_in_lavorazione t_segnalazioni_in_lavorazione_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_segnalazioni_in_lavorazione
    ADD CONSTRAINT t_segnalazioni_in_lavorazione_pkey PRIMARY KEY (id);


--
-- Name: t_segnalazioni t_segnalazioni_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_segnalazioni
    ADD CONSTRAINT t_segnalazioni_pkey PRIMARY KEY (id);


--
-- Name: t_sopralluoghi_mobili t_sopralluoghi__mobili_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_sopralluoghi_mobili
    ADD CONSTRAINT t_sopralluoghi__mobili_pkey PRIMARY KEY (id);


--
-- Name: t_sopralluoghi_mobili_richiesta_cambi t_sopralluoghi_mobili_richiesta_cambi_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_sopralluoghi_mobili_richiesta_cambi
    ADD CONSTRAINT t_sopralluoghi_mobili_richiesta_cambi_pkey PRIMARY KEY (id_sopralluogo, data_ora);


--
-- Name: t_sopralluoghi t_sopralluoghi_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_sopralluoghi
    ADD CONSTRAINT t_sopralluoghi_pkey PRIMARY KEY (id);


--
-- Name: t_sopralluoghi_richiesta_cambi t_sopralluoghi_richiesta_cambi_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_sopralluoghi_richiesta_cambi
    ADD CONSTRAINT t_sopralluoghi_richiesta_cambi_pkey PRIMARY KEY (id_sopralluogo, data_ora);


--
-- Name: stato_incarichi t_stato_incarichi; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.stato_incarichi
    ADD CONSTRAINT t_stato_incarichi PRIMARY KEY (id_incarico, id_stato_incarico, data_ora_stato);


--
-- Name: stato_incarichi_interni t_stato_incarichi_interni; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.stato_incarichi_interni
    ADD CONSTRAINT t_stato_incarichi_interni PRIMARY KEY (id_incarico, id_stato_incarico, data_ora_stato);


--
-- Name: t_storico_mail t_storico_mail_pk; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_storico_mail
    ADD CONSTRAINT t_storico_mail_pk PRIMARY KEY (id);


--
-- Name: t_storico_segnalazioni_in_lavorazione t_storico_segnalazioni_in_lavorazione_pkey; Type: CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.t_storico_segnalazioni_in_lavorazione
    ADD CONSTRAINT t_storico_segnalazioni_in_lavorazione_pkey PRIMARY KEY (id_segnalazione_in_lavorazione, data_ora);


--
-- Name: utenti_esterni personale_volontario_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.utenti_esterni
    ADD CONSTRAINT personale_volontario_pkey PRIMARY KEY (cf);


--
-- Name: t_mail_squadre pkey_t_mail_incarichi_interni; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_mail_squadre
    ADD CONSTRAINT pkey_t_mail_incarichi_interni PRIMARY KEY (cod, mail);


--
-- Name: t_mail_meteo pkey_t_mail_meteo; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_mail_meteo
    ADD CONSTRAINT pkey_t_mail_meteo PRIMARY KEY (id);


--
-- Name: t_telefono_squadre pkey_t_telefono_squadre; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_telefono_squadre
    ADD CONSTRAINT pkey_t_telefono_squadre PRIMARY KEY (cod, telefono, matricola_cf);


--
-- Name: profili_utilizzatore profili_utilizzatore_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.profili_utilizzatore
    ADD CONSTRAINT profili_utilizzatore_pkey PRIMARY KEY (id);


--
-- Name: t_stato_squadre stato_squadre_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_stato_squadre
    ADD CONSTRAINT stato_squadre_pkey PRIMARY KEY (id);


--
-- Name: t_centrali t_centrali_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_centrali
    ADD CONSTRAINT t_centrali_pkey PRIMARY KEY (cod);


--
-- Name: t_componenti_squadre t_componenti_squadre_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_componenti_squadre
    ADD CONSTRAINT t_componenti_squadre_pkey PRIMARY KEY (id_squadra, matricola_cf, data_start);


--
-- Name: t_convocazione t_convocazione_pk; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_convocazione
    ADD CONSTRAINT t_convocazione_pk PRIMARY KEY (id);


--
-- Name: t_mail_incarichi t_mail_incarichi_pk; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_mail_incarichi
    ADD CONSTRAINT t_mail_incarichi_pk PRIMARY KEY (cod, mail);


--
-- Name: t_posizioni_squadre t_posizioni_squadre_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_posizioni_squadre
    ADD CONSTRAINT t_posizioni_squadre_pkey PRIMARY KEY (id_squadra, last_update);


--
-- Name: t_presenze t_presenze_pk; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_presenze
    ADD CONSTRAINT t_presenze_pk PRIMARY KEY (id);


--
-- Name: t_reperibili t_reperibili_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_reperibili
    ADD CONSTRAINT t_reperibili_pkey PRIMARY KEY (id);


--
-- Name: t_squadre t_squadre_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_squadre
    ADD CONSTRAINT t_squadre_pkey PRIMARY KEY (id);


--
-- Name: t_storico_squadre t_storico_squadre_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.t_storico_squadre
    ADD CONSTRAINT t_storico_squadre_pkey PRIMARY KEY (id_squadra, data_ora);


--
-- Name: uo_1_livello uo_1_livello_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.uo_1_livello
    ADD CONSTRAINT uo_1_livello_pkey PRIMARY KEY (id1);


--
-- Name: uo_2_livello uo_2_livello_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.uo_2_livello
    ADD CONSTRAINT uo_2_livello_pkey PRIMARY KEY (id1, id2);


--
-- Name: uo_3_livello uo_3_livello_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.uo_3_livello
    ADD CONSTRAINT uo_3_livello_pkey PRIMARY KEY (id1, id2, id3);


--
-- Name: utenti_coc_eliminati utenti_coc_eliminati_pk; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.utenti_coc_eliminati
    ADD CONSTRAINT utenti_coc_eliminati_pk PRIMARY KEY (id, matricola_cf);


--
-- Name: utenti_coc utenti_coc_pk; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.utenti_coc
    ADD CONSTRAINT utenti_coc_pk PRIMARY KEY (id, matricola_cf);


--
-- Name: utenti_esterni_copy utenti_esterni_copy_pk; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.utenti_esterni_copy
    ADD CONSTRAINT utenti_esterni_copy_pk PRIMARY KEY (cf);


--
-- Name: utenti_esterni_eliminati utenti_esterni_eliminiati_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.utenti_esterni_eliminati
    ADD CONSTRAINT utenti_esterni_eliminiati_pkey PRIMARY KEY (cf);


--
-- Name: utenti_input_telegram utenti_input_telegram_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.utenti_input_telegram
    ADD CONSTRAINT utenti_input_telegram_pkey PRIMARY KEY (matricola_cf);


--
-- Name: utenti_message_update utenti_message_update_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.utenti_message_update
    ADD CONSTRAINT utenti_message_update_pkey PRIMARY KEY (matricola_cf);


--
-- Name: utenti_sistema utenti_sistema_pkey; Type: CONSTRAINT; Schema: users; Owner: -
--

ALTER TABLE ONLY users.utenti_sistema
    ADD CONSTRAINT utenti_sistema_pkey PRIMARY KEY (matricola_cf);


--
-- Name: comuni_italia comuni_pkey; Type: CONSTRAINT; Schema: varie; Owner: -
--

ALTER TABLE ONLY varie.comuni_italia
    ADD CONSTRAINT comuni_pkey PRIMARY KEY ("Codice Comune formato alfanumerico");


--
-- Name: dipendenti2 dipendenti2_pkey; Type: CONSTRAINT; Schema: varie; Owner: -
--

ALTER TABLE ONLY varie.dipendenti2
    ADD CONSTRAINT dipendenti2_pkey PRIMARY KEY (gid);


--
-- Name: dipendenti dipendenti_matricola_uc; Type: CONSTRAINT; Schema: varie; Owner: -
--

ALTER TABLE ONLY varie.dipendenti
    ADD CONSTRAINT dipendenti_matricola_uc UNIQUE (matricola);


--
-- Name: dipendenti dipendenti_pkey; Type: CONSTRAINT; Schema: varie; Owner: -
--

ALTER TABLE ONLY varie.dipendenti
    ADD CONSTRAINT dipendenti_pkey PRIMARY KEY (gid);


--
-- Name: dipendenti_storici dipendenti_storici_pkey; Type: CONSTRAINT; Schema: varie; Owner: -
--

ALTER TABLE ONLY varie.dipendenti_storici
    ADD CONSTRAINT dipendenti_storici_pkey PRIMARY KEY (gid);


--
-- Name: t_log id_log_pkey; Type: CONSTRAINT; Schema: varie; Owner: -
--

ALTER TABLE ONLY varie.t_log
    ADD CONSTRAINT id_log_pkey PRIMARY KEY (id);


--
-- Name: stati_2018 stati_pkey; Type: CONSTRAINT; Schema: varie; Owner: -
--

ALTER TABLE ONLY varie.stati_2018
    ADD CONSTRAINT stati_pkey PRIMARY KEY (cod);


--
-- Name: fki_join_municipi_fk_eventi; Type: INDEX; Schema: eventi; Owner: -
--

CREATE INDEX fki_join_municipi_fk_eventi ON eventi.join_municipi USING btree (id_evento);


--
-- Name: fki_join_municipi_fk_municipi; Type: INDEX; Schema: eventi; Owner: -
--

CREATE INDEX fki_join_municipi_fk_municipi ON eventi.join_municipi USING btree (id_municipio);


--
-- Name: fki_join_tipo_allerta_fk_eventi; Type: INDEX; Schema: eventi; Owner: -
--

CREATE INDEX fki_join_tipo_allerta_fk_eventi ON eventi.join_tipo_allerta USING btree (id_evento);


--
-- Name: fki_join_tipo_allerta_fk_tipo_allerta; Type: INDEX; Schema: eventi; Owner: -
--

CREATE INDEX fki_join_tipo_allerta_fk_tipo_allerta ON eventi.join_tipo_allerta USING btree (id_tipo_allerta);


--
-- Name: fki_join_tipo_evento_fk_eventi; Type: INDEX; Schema: eventi; Owner: -
--

CREATE INDEX fki_join_tipo_evento_fk_eventi ON eventi.join_tipo_evento USING btree (id_evento);


--
-- Name: fki_join_tipo_evento_fk_tipo_evento; Type: INDEX; Schema: eventi; Owner: -
--

CREATE INDEX fki_join_tipo_evento_fk_tipo_evento ON eventi.join_tipo_evento USING btree (id_tipo_evento);


--
-- Name: fki_join_tipo_foc_fk_eventi; Type: INDEX; Schema: eventi; Owner: -
--

CREATE INDEX fki_join_tipo_foc_fk_eventi ON eventi.join_tipo_foc USING btree (id_evento);


--
-- Name: fki_join_tipo_foc_fk_tipo_foc; Type: INDEX; Schema: eventi; Owner: -
--

CREATE INDEX fki_join_tipo_foc_fk_tipo_foc ON eventi.join_tipo_foc USING btree (id_tipo_foc);


--
-- Name: fki_t_attivazione_nverde_fk_eventi; Type: INDEX; Schema: eventi; Owner: -
--

CREATE INDEX fki_t_attivazione_nverde_fk_eventi ON eventi.t_attivazione_nverde USING btree (id_evento);


--
-- Name: fki_t_note_eventi_fk_eventi; Type: INDEX; Schema: eventi; Owner: -
--

CREATE INDEX fki_t_note_eventi_fk_eventi ON eventi.t_note_eventi USING btree (id_evento);


--
-- Name: aree_verdi_geom_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX aree_verdi_geom_geom_idx ON geodb.aree_verdi USING gist (geom);


--
-- Name: civici_geom_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX civici_geom_geom_idx ON geodb.civici USING gist (geom);


--
-- Name: civici_wkb_geometry_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX civici_wkb_geometry_geom_idx ON geodb.civici_wfs USING gist (wkb_geometry);


--
-- Name: edifici_geom_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX edifici_geom_geom_idx ON geodb.edifici USING gist (geom);


--
-- Name: elemento_stradale_geom_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX elemento_stradale_geom_geom_idx ON geodb.elemento_stradale USING gist (geom);


--
-- Name: fiumi _geom_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX "fiumi
_geom_geom_idx" ON geodb.fiumi USING gist (geom);


--
-- Name: presidi_geom_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX presidi_geom_geom_idx ON geodb.presidi USING gist (geom);


--
-- Name: punti_monitoraggio_geom_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX punti_monitoraggio_geom_geom_idx ON geodb.punti_monitoraggio USING gist (geom);


--
-- Name: punti_monitoraggio_ok_geom_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX punti_monitoraggio_ok_geom_geom_idx ON geodb.punti_monitoraggio_ok USING gist (geom);


--
-- Name: punti_monitoraggio_ok_old_geom_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX punti_monitoraggio_ok_old_geom_geom_idx ON geodb.punti_monitoraggio_ok_old USING gist (geom);


--
-- Name: rir_impianti_geom_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX rir_impianti_geom_geom_idx ON geodb.rir_impianti USING gist (geom);


--
-- Name: sidx_municipi_geom; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX sidx_municipi_geom ON geodb.municipi USING gist (geom);


--
-- Name: sottopassi_geom_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX sottopassi_geom_geom_idx ON geodb.sottopassi USING gist (geom);


--
-- Name: tracciato_stradale_geom_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX tracciato_stradale_geom_geom_idx ON geodb.tracciato_stradale USING gist (geom);


--
-- Name: vie_wkb_geometry_geom_idx; Type: INDEX; Schema: geodb; Owner: -
--

CREATE INDEX vie_wkb_geometry_geom_idx ON geodb.vie_wfs USING gist (wkb_geometry);


--
-- Name: mediatore:v_civici_dbt_angolo_geoserver_wkb_geometry_geom_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX "mediatore:v_civici_dbt_angolo_geoserver_wkb_geometry_geom_idx" ON public."mediatore:v_civici_dbt_angolo_geoserver" USING gist (wkb_geometry);


--
-- Name: fki_t_aggiornamento_meteo_fk_eventi; Type: INDEX; Schema: report; Owner: -
--

CREATE INDEX fki_t_aggiornamento_meteo_fk_eventi ON report.t_aggiornamento_meteo USING btree (id_evento);


--
-- Name: fki_t_comunicazione_fk_eventi; Type: INDEX; Schema: report; Owner: -
--

CREATE INDEX fki_t_comunicazione_fk_eventi ON report.t_comunicazione USING btree (id_evento);


--
-- Name: fki_join_incarichi_interni_squadre_fk_incarico; Type: INDEX; Schema: segnalazioni; Owner: -
--

CREATE INDEX fki_join_incarichi_interni_squadre_fk_incarico ON segnalazioni.join_incarichi_interni_squadra USING btree (id_incarico);


--
-- Name: fki_join_incarichi_interni_squadre_fk_squadra; Type: INDEX; Schema: segnalazioni; Owner: -
--

CREATE INDEX fki_join_incarichi_interni_squadre_fk_squadra ON segnalazioni.join_incarichi_interni_squadra USING btree (id_squadra);


--
-- Name: fki_join_incarico_provvedimenti_cautelari_fk_incarico; Type: INDEX; Schema: segnalazioni; Owner: -
--

CREATE INDEX fki_join_incarico_provvedimenti_cautelari_fk_incarico ON segnalazioni.join_incarico_provvedimenti_cautelari USING btree (id_incarico);


--
-- Name: fki_join_incarico_provvedimenti_cautelari_fk_provvedimento; Type: INDEX; Schema: segnalazioni; Owner: -
--

CREATE INDEX fki_join_incarico_provvedimenti_cautelari_fk_provvedimento ON segnalazioni.join_incarico_provvedimenti_cautelari USING btree (id_provvedimento);


--
-- Name: fki_join_oggetto_rischio_fk_segnalazione; Type: INDEX; Schema: segnalazioni; Owner: -
--

CREATE INDEX fki_join_oggetto_rischio_fk_segnalazione ON segnalazioni.join_oggetto_rischio USING btree (id_segnalazione);


--
-- Name: fki_join_oggetto_rischio_fk_tipo_oggetto; Type: INDEX; Schema: segnalazioni; Owner: -
--

CREATE INDEX fki_join_oggetto_rischio_fk_tipo_oggetto ON segnalazioni.join_oggetto_rischio USING btree (id_tipo_oggetto);


--
-- Name: fki_join_segnalazioni_in_lavorazione_fk_segnalazione; Type: INDEX; Schema: segnalazioni; Owner: -
--

CREATE INDEX fki_join_segnalazioni_in_lavorazione_fk_segnalazione ON segnalazioni.join_segnalazioni_in_lavorazione USING btree (id_segnalazione);


--
-- Name: fki_join_segnalazioni_in_lavorazione_fk_segnalazione_in_lavoraz; Type: INDEX; Schema: segnalazioni; Owner: -
--

CREATE INDEX fki_join_segnalazioni_in_lavorazione_fk_segnalazione_in_lavoraz ON segnalazioni.join_segnalazioni_in_lavorazione USING btree (id_segnalazione_in_lavorazione);


--
-- Name: idx_matricola; Type: INDEX; Schema: users; Owner: -
--

CREATE INDEX idx_matricola ON users.utenti_sistema USING btree (matricola_cf);


--
-- Name: nome_squadra_idx; Type: INDEX; Schema: users; Owner: -
--

CREATE INDEX nome_squadra_idx ON users.t_squadre USING btree (nome);


--
-- Name: dipendenti_storici_cognome_idx; Type: INDEX; Schema: varie; Owner: -
--

CREATE INDEX dipendenti_storici_cognome_idx ON varie.dipendenti_storici USING btree (cognome);


--
-- Name: idx_cognome_dipendente; Type: INDEX; Schema: varie; Owner: -
--

CREATE INDEX idx_cognome_dipendente ON varie.dipendenti USING btree (cognome);


--
-- Name: v_squadre _RETURN; Type: RULE; Schema: users; Owner: -
--

CREATE OR REPLACE VIEW users.v_squadre AS
 SELECT s.id,
    s.nome,
    ((((e.descrizione)::text || ' ('::text) || s.id_evento) || ')'::text) AS evento,
    s.id_stato,
    st.descrizione AS stato,
    s.cod_afferenza,
    a.descrizione AS afferenza,
    ( SELECT count(c.id_squadra) AS count
           FROM users.v_componenti_squadre c
          WHERE ((c.id_squadra = s.id) AND (c.data_end IS NULL))) AS num_componenti,
    a.profilo,
    ( SELECT string_agg(concat(cc.cognome, ' ', cc.nome), ', '::text) AS string_agg
           FROM users.v_componenti_squadre cc
          WHERE ((cc.id_squadra = s.id) AND (cc.data_end IS NULL))) AS componenti,
    s.da_nascondere
   FROM (((users.t_squadre s
     JOIN varie.v_incarichi_mail_old a ON (((s.cod_afferenza)::text = a.cod)))
     LEFT JOIN eventi.v_eventi e ON ((s.id_evento = e.id)))
     JOIN users.t_stato_squadre st ON ((s.id_stato = st.id)))
  WHERE ((e.valido = true) OR (e.valido IS NULL) OR (s.id_evento IS NULL))
  GROUP BY s.id, s.nome, ((((e.descrizione)::text || ' ('::text) || s.id_evento) || ')'::text), s.id_stato, st.descrizione, s.cod_afferenza, a.descrizione, a.profilo;


--
-- Name: join_municipi join_municipi_fk_eventi; Type: FK CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.join_municipi
    ADD CONSTRAINT join_municipi_fk_eventi FOREIGN KEY (id_evento) REFERENCES eventi.t_eventi(id);


--
-- Name: join_municipi join_municipi_fk_municipi; Type: FK CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.join_municipi
    ADD CONSTRAINT join_municipi_fk_municipi FOREIGN KEY (id_municipio) REFERENCES geodb.municipi(codice_mun);


--
-- Name: join_tipo_allerta join_tipo_allerta_fk_eventi; Type: FK CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.join_tipo_allerta
    ADD CONSTRAINT join_tipo_allerta_fk_eventi FOREIGN KEY (id_evento) REFERENCES eventi.t_eventi(id);


--
-- Name: join_tipo_allerta join_tipo_allerta_fk_tipo_allerta; Type: FK CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.join_tipo_allerta
    ADD CONSTRAINT join_tipo_allerta_fk_tipo_allerta FOREIGN KEY (id_tipo_allerta) REFERENCES eventi.tipo_allerta(id);


--
-- Name: join_tipo_evento join_tipo_evento_fk_eventi; Type: FK CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.join_tipo_evento
    ADD CONSTRAINT join_tipo_evento_fk_eventi FOREIGN KEY (id_evento) REFERENCES eventi.t_eventi(id);


--
-- Name: join_tipo_evento join_tipo_evento_fk_tipo_evento; Type: FK CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.join_tipo_evento
    ADD CONSTRAINT join_tipo_evento_fk_tipo_evento FOREIGN KEY (id_tipo_evento) REFERENCES eventi.tipo_evento(id);


--
-- Name: join_tipo_foc join_tipo_foc_fk_eventi; Type: FK CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.join_tipo_foc
    ADD CONSTRAINT join_tipo_foc_fk_eventi FOREIGN KEY (id_evento) REFERENCES eventi.t_eventi(id);


--
-- Name: join_tipo_foc join_tipo_foc_fk_tipo_foc; Type: FK CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.join_tipo_foc
    ADD CONSTRAINT join_tipo_foc_fk_tipo_foc FOREIGN KEY (id_tipo_foc) REFERENCES eventi.tipo_foc(id);


--
-- Name: t_attivazione_nverde t_attivazione_nverde_fk_eventi; Type: FK CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.t_attivazione_nverde
    ADD CONSTRAINT t_attivazione_nverde_fk_eventi FOREIGN KEY (id_evento) REFERENCES eventi.t_eventi(id);


--
-- Name: t_note_eventi t_note_eventi_fk_eventi; Type: FK CONSTRAINT; Schema: eventi; Owner: -
--

ALTER TABLE ONLY eventi.t_note_eventi
    ADD CONSTRAINT t_note_eventi_fk_eventi FOREIGN KEY (id_evento) REFERENCES eventi.t_eventi(id);


--
-- Name: t_aggiornamento_meteo t_aggiornamento_meteo_fk_eventi; Type: FK CONSTRAINT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_aggiornamento_meteo
    ADD CONSTRAINT t_aggiornamento_meteo_fk_eventi FOREIGN KEY (id_evento) REFERENCES eventi.t_eventi(id);


--
-- Name: t_comunicazione t_comunicazione_fk_eventi; Type: FK CONSTRAINT; Schema: report; Owner: -
--

ALTER TABLE ONLY report.t_comunicazione
    ADD CONSTRAINT t_comunicazione_fk_eventi FOREIGN KEY (id_evento) REFERENCES eventi.t_eventi(id);


--
-- Name: join_incarichi_interni_squadra join_incarichi_interni_squadre_fk_incarico; Type: FK CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_incarichi_interni_squadra
    ADD CONSTRAINT join_incarichi_interni_squadre_fk_incarico FOREIGN KEY (id_incarico) REFERENCES segnalazioni.t_incarichi_interni(id);


--
-- Name: join_incarichi_interni_squadra join_incarichi_interni_squadre_fk_squadra; Type: FK CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_incarichi_interni_squadra
    ADD CONSTRAINT join_incarichi_interni_squadre_fk_squadra FOREIGN KEY (id_squadra) REFERENCES users.t_squadre(id);


--
-- Name: join_incarico_provvedimenti_cautelari join_incarico_provvedimenti_cautelari_fk_incarico; Type: FK CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_incarico_provvedimenti_cautelari
    ADD CONSTRAINT join_incarico_provvedimenti_cautelari_fk_incarico FOREIGN KEY (id_incarico) REFERENCES segnalazioni.t_incarichi(id);


--
-- Name: join_incarico_provvedimenti_cautelari join_incarico_provvedimenti_cautelari_fk_provvedimento; Type: FK CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_incarico_provvedimenti_cautelari
    ADD CONSTRAINT join_incarico_provvedimenti_cautelari_fk_provvedimento FOREIGN KEY (id_provvedimento) REFERENCES segnalazioni.t_provvedimenti_cautelari(id);


--
-- Name: join_oggetto_rischio join_oggetto_rischio_fk_segnalazione; Type: FK CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_oggetto_rischio
    ADD CONSTRAINT join_oggetto_rischio_fk_segnalazione FOREIGN KEY (id_segnalazione) REFERENCES segnalazioni.t_segnalazioni(id);


--
-- Name: join_oggetto_rischio join_oggetto_rischio_fk_tipo_oggetto; Type: FK CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_oggetto_rischio
    ADD CONSTRAINT join_oggetto_rischio_fk_tipo_oggetto FOREIGN KEY (id_tipo_oggetto) REFERENCES segnalazioni.tipo_oggetti_rischio(id);


--
-- Name: join_segnalazioni_in_lavorazione join_segnalazioni_in_lavorazione_fk_segnalazione; Type: FK CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_segnalazioni_in_lavorazione
    ADD CONSTRAINT join_segnalazioni_in_lavorazione_fk_segnalazione FOREIGN KEY (id_segnalazione) REFERENCES segnalazioni.t_segnalazioni(id);


--
-- Name: join_segnalazioni_in_lavorazione join_segnalazioni_in_lavorazione_fk_segnalazione_in_lavorazione; Type: FK CONSTRAINT; Schema: segnalazioni; Owner: -
--

ALTER TABLE ONLY segnalazioni.join_segnalazioni_in_lavorazione
    ADD CONSTRAINT join_segnalazioni_in_lavorazione_fk_segnalazione_in_lavorazione FOREIGN KEY (id_segnalazione_in_lavorazione) REFERENCES segnalazioni.t_segnalazioni_in_lavorazione(id);


--
-- PostgreSQL database dump complete
--

