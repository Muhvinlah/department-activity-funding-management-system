--
-- PostgreSQL database dump
--

\restrict oaiqkj7g8tNs7vkaQ5ZXK6VGe2s7uKkLohIlNkMMCyJxBX3pwfTThI9SD5Ts0cB

-- Dumped from database version 17.7
-- Dumped by pg_dump version 17.7

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: approval_action; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.approval_action AS ENUM (
    'approved',
    'rejected',
    'request_revision'
);


--
-- Name: status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.status AS ENUM (
    'submitted',
    'reviewed_by_secretary',
    'verified_by_admin',
    'approved_by_head',
    'rejected',
    'needs_revision_by_secretary',
    'needs_revision_by_admin',
    'needs_revision_by_head'
);


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: activity_category; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activity_category (
    category_id bigint NOT NULL,
    category_def character varying(100) NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) without time zone
);


--
-- Name: activity_category_category_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activity_category_category_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activity_category_category_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.activity_category_category_id_seq OWNED BY public.activity_category.category_id;


--
-- Name: annual_budget; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.annual_budget (
    budget_id bigint NOT NULL,
    tahun character varying(4) NOT NULL,
    budget numeric(15,2) NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) without time zone
);


--
-- Name: annual_budget_budget_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.annual_budget_budget_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: annual_budget_budget_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.annual_budget_budget_id_seq OWNED BY public.annual_budget.budget_id;


--
-- Name: attachment; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.attachment (
    attach_id bigint NOT NULL,
    file_path character varying(500) NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) without time zone,
    tor_id bigint,
    lpj_id bigint,
    file_name character varying(255),
    file_type character varying(50)
);


--
-- Name: attachment_attach_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.attachment_attach_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: attachment_attach_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.attachment_attach_id_seq OWNED BY public.attachment.attach_id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: lpj; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.lpj (
    lpj_id bigint NOT NULL,
    activity_result text NOT NULL,
    activity_evaluation text NOT NULL,
    actual_date date NOT NULL,
    budget_used numeric(15,2) NOT NULL,
    status character varying(50) NOT NULL,
    current_stage character varying(50) DEFAULT 'under_review'::character varying NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) without time zone,
    tor_id bigint,
    user_id character varying(10)
);


--
-- Name: lpj_approv; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.lpj_approv (
    approv_id bigint NOT NULL,
    lpj_id bigint,
    user_id character varying(10),
    role_id bigint,
    status character varying(50) NOT NULL,
    catatan text,
    action character varying(50) NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: lpj_approv_approv_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.lpj_approv_approv_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: lpj_approv_approv_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.lpj_approv_approv_id_seq OWNED BY public.lpj_approv.approv_id;


--
-- Name: lpj_lpj_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.lpj_lpj_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: lpj_lpj_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.lpj_lpj_id_seq OWNED BY public.lpj.lpj_id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    role_id bigint NOT NULL,
    role_def character varying(50) NOT NULL
);


--
-- Name: roles_role_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.roles_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: roles_role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.roles_role_id_seq OWNED BY public.roles.role_id;


--
-- Name: status_hist; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.status_hist (
    hist_id bigint NOT NULL,
    status character varying(50) NOT NULL,
    catatan text,
    timestamp_aksi timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) without time zone,
    user_id character varying(10),
    tor_id bigint,
    lpj_id bigint
);


--
-- Name: status_hist_hist_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.status_hist_hist_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: status_hist_hist_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.status_hist_hist_id_seq OWNED BY public.status_hist.hist_id;


--
-- Name: tor; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tor (
    tor_id bigint NOT NULL,
    activity_name character varying(255) NOT NULL,
    activity_background text NOT NULL,
    activity_purpose text NOT NULL,
    participant text NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    budget_submitted numeric(15,2) NOT NULL,
    pic character varying(100) NOT NULL,
    status character varying(50) NOT NULL,
    current_stage character varying(50) DEFAULT 'under_review'::character varying NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) without time zone,
    category_id bigint,
    user_id character varying(10),
    budget_id bigint
);


--
-- Name: tor_approv; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tor_approv (
    approv_id bigint NOT NULL,
    tor_id bigint,
    user_id character varying(10),
    role_id bigint,
    status character varying(50) NOT NULL,
    catatan text,
    action character varying(50) NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: tor_approv_approv_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tor_approv_approv_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tor_approv_approv_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tor_approv_approv_id_seq OWNED BY public.tor_approv.approv_id;


--
-- Name: tor_tor_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tor_tor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tor_tor_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tor_tor_id_seq OWNED BY public.tor.tor_id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    user_id character varying(10) NOT NULL,
    full_name character varying(100) NOT NULL,
    email character varying(100) NOT NULL,
    password character varying(255) NOT NULL,
    role_id bigint,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at timestamp(0) without time zone
);


--
-- Name: COLUMN users.user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.user_id IS 'NIM (10 digits)';


--
-- Name: COLUMN users.password; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.password IS 'Hashed password';


--
-- Name: activity_category category_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_category ALTER COLUMN category_id SET DEFAULT nextval('public.activity_category_category_id_seq'::regclass);


--
-- Name: annual_budget budget_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.annual_budget ALTER COLUMN budget_id SET DEFAULT nextval('public.annual_budget_budget_id_seq'::regclass);


--
-- Name: attachment attach_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attachment ALTER COLUMN attach_id SET DEFAULT nextval('public.attachment_attach_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: lpj lpj_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lpj ALTER COLUMN lpj_id SET DEFAULT nextval('public.lpj_lpj_id_seq'::regclass);


--
-- Name: lpj_approv approv_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lpj_approv ALTER COLUMN approv_id SET DEFAULT nextval('public.lpj_approv_approv_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: roles role_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles ALTER COLUMN role_id SET DEFAULT nextval('public.roles_role_id_seq'::regclass);


--
-- Name: status_hist hist_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.status_hist ALTER COLUMN hist_id SET DEFAULT nextval('public.status_hist_hist_id_seq'::regclass);


--
-- Name: tor tor_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tor ALTER COLUMN tor_id SET DEFAULT nextval('public.tor_tor_id_seq'::regclass);


--
-- Name: tor_approv approv_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tor_approv ALTER COLUMN approv_id SET DEFAULT nextval('public.tor_approv_approv_id_seq'::regclass);


--
-- Name: activity_category activity_category_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_category
    ADD CONSTRAINT activity_category_pkey PRIMARY KEY (category_id);


--
-- Name: annual_budget annual_budget_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.annual_budget
    ADD CONSTRAINT annual_budget_pkey PRIMARY KEY (budget_id);


--
-- Name: attachment attachment_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attachment
    ADD CONSTRAINT attachment_pkey PRIMARY KEY (attach_id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: lpj_approv lpj_approv_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lpj_approv
    ADD CONSTRAINT lpj_approv_pkey PRIMARY KEY (approv_id);


--
-- Name: lpj lpj_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lpj
    ADD CONSTRAINT lpj_pkey PRIMARY KEY (lpj_id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (role_id);


--
-- Name: roles roles_role_def_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_role_def_unique UNIQUE (role_def);


--
-- Name: status_hist status_hist_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.status_hist
    ADD CONSTRAINT status_hist_pkey PRIMARY KEY (hist_id);


--
-- Name: tor_approv tor_approv_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tor_approv
    ADD CONSTRAINT tor_approv_pkey PRIMARY KEY (approv_id);


--
-- Name: tor tor_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tor
    ADD CONSTRAINT tor_pkey PRIMARY KEY (tor_id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: attachment attachment_lpj_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attachment
    ADD CONSTRAINT attachment_lpj_id_foreign FOREIGN KEY (lpj_id) REFERENCES public.lpj(lpj_id) ON DELETE CASCADE;


--
-- Name: attachment attachment_tor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attachment
    ADD CONSTRAINT attachment_tor_id_foreign FOREIGN KEY (tor_id) REFERENCES public.tor(tor_id) ON DELETE CASCADE;


--
-- Name: lpj_approv lpj_approv_lpj_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lpj_approv
    ADD CONSTRAINT lpj_approv_lpj_id_foreign FOREIGN KEY (lpj_id) REFERENCES public.lpj(lpj_id) ON DELETE CASCADE;


--
-- Name: lpj_approv lpj_approv_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lpj_approv
    ADD CONSTRAINT lpj_approv_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(role_id) ON DELETE SET NULL;


--
-- Name: lpj_approv lpj_approv_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lpj_approv
    ADD CONSTRAINT lpj_approv_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: lpj lpj_tor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lpj
    ADD CONSTRAINT lpj_tor_id_foreign FOREIGN KEY (tor_id) REFERENCES public.tor(tor_id) ON DELETE CASCADE;


--
-- Name: lpj lpj_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lpj
    ADD CONSTRAINT lpj_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: status_hist status_hist_lpj_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.status_hist
    ADD CONSTRAINT status_hist_lpj_id_foreign FOREIGN KEY (lpj_id) REFERENCES public.lpj(lpj_id) ON DELETE CASCADE;


--
-- Name: status_hist status_hist_tor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.status_hist
    ADD CONSTRAINT status_hist_tor_id_foreign FOREIGN KEY (tor_id) REFERENCES public.tor(tor_id) ON DELETE CASCADE;


--
-- Name: status_hist status_hist_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.status_hist
    ADD CONSTRAINT status_hist_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: tor_approv tor_approv_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tor_approv
    ADD CONSTRAINT tor_approv_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(role_id) ON DELETE SET NULL;


--
-- Name: tor_approv tor_approv_tor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tor_approv
    ADD CONSTRAINT tor_approv_tor_id_foreign FOREIGN KEY (tor_id) REFERENCES public.tor(tor_id) ON DELETE CASCADE;


--
-- Name: tor_approv tor_approv_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tor_approv
    ADD CONSTRAINT tor_approv_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: tor tor_budget_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tor
    ADD CONSTRAINT tor_budget_id_foreign FOREIGN KEY (budget_id) REFERENCES public.annual_budget(budget_id) ON DELETE SET NULL;


--
-- Name: tor tor_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tor
    ADD CONSTRAINT tor_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.activity_category(category_id) ON DELETE SET NULL;


--
-- Name: tor tor_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tor
    ADD CONSTRAINT tor_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: users users_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(role_id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict oaiqkj7g8tNs7vkaQ5ZXK6VGe2s7uKkLohIlNkMMCyJxBX3pwfTThI9SD5Ts0cB

--
-- PostgreSQL database dump
--

\restrict eSjx9RPUUpwUdDaFub4Ct70rmYypBxopEayn7b6h0UDFAZSZCapvgcce247Wgz4

-- Dumped from database version 17.7
-- Dumped by pg_dump version 17.7

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
15	0001_01_01_000001_create_cache_table	1
16	0001_01_01_000002_create_jobs_table	1
17	01_create_enum_types	1
18	02_create_roles_table	1
19	03_create_users_table	1
20	04_create_activity_category_table	1
21	05_create_annual_budget_table	1
22	06_create_tor_table	1
23	07_create_lpj_table	1
24	08_create_attachment_table	1
25	09_create_status_hist_table	1
26	10_create_tor_approv_table	1
27	11_create_lpj_approv_table	1
28	2025_11_28_000002_add_file_metadata_to_attachment	1
\.


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 28, true);


--
-- PostgreSQL database dump complete
--

\unrestrict eSjx9RPUUpwUdDaFub4Ct70rmYypBxopEayn7b6h0UDFAZSZCapvgcce247Wgz4

