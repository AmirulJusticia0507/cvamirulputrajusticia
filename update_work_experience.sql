-- ============================================================
-- Perbaikan data work_experience: hanya DISKOMINFO DIY yang "Present"
-- (semua pengalaman lain mendapat end_date sesuai CV Amirul Putra Justicia)
--
-- Cara pakai: jalankan file ini di Beekeeper Studio (db: cv_db)
-- atau via psql:  psql -U postgres -d cv_db -f update_work_experience.sql
--
-- PERINGATAN: file ini me-reset semua baris di tabel work_experience.
-- ============================================================

TRUNCATE work_experience RESTART IDENTITY;

INSERT INTO work_experience
    (company, position, start_date, end_date, present, description, location, status_kerja, project, stack)
VALUES
    (
        'PT. Syariah Al Amin Life Insurance',
        'Web Systems Engineer',
        '2018-10-01',
        '2019-10-31',
        'f',
        'Delivered internal office systems in an early full-stack development role.',
        'Yogyakarta, Indonesia',
        'Full-time',
        '',
        ''
    ),
    (
        'Sekretariat DPRD Sleman',
        'Web Systems Engineer',
        '2020-01-01',
        '2022-10-31',
        'f',
        'Built JDIH, a legal documentation and information system for regional legislation. Developed a visitor-management system and a DPRD work-meeting (rapat kerja) coordination system.',
        'Sleman, Indonesia',
        'Full-time',
        '',
        ''
    ),
    (
        'PT. ASI Pudjiastuti Aviation (Susi Air)',
        'Web Systems Engineer',
        '2022-10-01',
        '2023-01-31',
        'f',
        'Contributed to a pilot digital project and supported general-affairs system needs for the airline.',
        'Yogyakarta, Indonesia',
        'Full-time',
        '',
        ''
    ),
    (
        'PT. Maybank Indonesia Finance Tbk.',
        'Web Systems Engineer',
        '2023-07-01',
        '2023-07-31',
        'f',
        'Provided short-term technical support during a peak operational period.',
        'Indonesia',
        '1-week engagement',
        '',
        ''
    ),
    (
        'PT. BPRS HIK MCI Yogyakarta',
        'Web Systems Engineer',
        '2023-09-01',
        '2024-04-30',
        'f',
        'Developed a mobile field-collection application, an internal web portal, and eForm/eVisit digital service modules. Delivered additional internal systems supporting company operations.',
        'Yogyakarta, Indonesia',
        'Full-time',
        '',
        ''
    ),
    (
        'CV Milimeter Yogyakarta',
        'Web Systems Engineer',
        '2024-06-01',
        '2024-07-31',
        'f',
        'Built the DEP (Digital Employee Performance) Service - a PHP/Laravel backend for office employee-performance management workflows.',
        'Yogyakarta, Indonesia',
        'Full-time',
        '',
        ''
    ),
    (
        'PT Kencana Konsep Indonesia',
        'Web Systems Engineer',
        '2024-10-01',
        '2026-01-31',
        'f',
        'Led development on a national-scale SPLP system and a multi-agency data consolidation platform. Built and maintained services using PHP (Laravel), Vue.js, Node.js, and Python (Django) with MySQL/PostgreSQL; applied REST/SOAP APIs and data-validation pipelines to keep cross-system data consistent.',
        'Bandung, West Java, Indonesia',
        'Contract',
        '',
        ''
    ),
    (
        'DISKOMINFO DIY',
        'Web Systems Engineer',
        '2026-03-01',
        NULL,
        't',
        'Maintain core government server infrastructure and an AI-powered CCTV monitoring portal for the DIY regional government. Develop public survey systems used across regional agencies. Build the DTSEN/DTKS social welfare registration platform (Dinsos DIY) - a Vue 3 + TypeScript system with multi-step registration, KRT/family-member data sync, location cascade logic, and PDF proof-of-registration generation - integrating data across multiple government agencies.',
        'Yogyakarta, Indonesia',
        'Full-time',
        '',
        ''
    );
