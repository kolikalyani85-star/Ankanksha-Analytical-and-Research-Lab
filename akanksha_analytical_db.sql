-- ============================================================
--  AKANKSHA ANALYTICAL & RESEARCH LAB — Full Database Schema
--  MySQL 8.0+  |  Generated May 2026
--  Import: mysql -u root -p akanksha_analytical < akanksha_analytical_db.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS akanksha_analytical
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE akanksha_analytical;

-- ============================================================
-- 1. CONTACT INQUIRIES
-- ============================================================
CREATE TABLE contact_inquiries (
    id               INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    inquiry_code     VARCHAR(20)      NOT NULL UNIQUE,
    full_name        VARCHAR(120)     NOT NULL,
    email            VARCHAR(180)     NOT NULL,
    phone            VARCHAR(20)      DEFAULT NULL,
    service_interest VARCHAR(120)     DEFAULT NULL,
    message          TEXT             NOT NULL,
    ip_address       VARCHAR(45)      DEFAULT NULL,
    status           ENUM('new','in_progress','resolved','spam') NOT NULL DEFAULT 'new',
    admin_notes      TEXT             DEFAULT NULL,
    submitted_at     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at      DATETIME         DEFAULT NULL,
    INDEX idx_status       (status),
    INDEX idx_submitted_at (submitted_at),
    INDEX idx_email        (email)
) ENGINE=InnoDB;

-- ============================================================
-- 2. SERVICE CATEGORIES
-- ============================================================
CREATE TABLE service_categories (
    id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80)  NOT NULL,
    icon_class  VARCHAR(60)  DEFAULT NULL,
    sort_order  TINYINT      NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ============================================================
-- 3. SERVICES
-- ============================================================
CREATE TABLE services (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id TINYINT UNSIGNED  NOT NULL,
    name        VARCHAR(150)      NOT NULL,
    description TEXT              DEFAULT NULL,
    is_active   TINYINT(1)        NOT NULL DEFAULT 1,
    sort_order  TINYINT           NOT NULL DEFAULT 0,
    created_at  DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_service_category
        FOREIGN KEY (category_id) REFERENCES service_categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_active   (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- 4. ACCREDITATIONS & CERTIFICATIONS
-- ============================================================
CREATE TABLE accreditations (
    id              TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(100) NOT NULL,
    issuing_body    VARCHAR(100) NOT NULL,
    description     VARCHAR(255) DEFAULT NULL,
    valid_from      DATE         DEFAULT NULL,
    valid_until     DATE         DEFAULT NULL,
    certificate_img VARCHAR(255) DEFAULT NULL,
    badge_icon      VARCHAR(60)  DEFAULT NULL,
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    sort_order      TINYINT      NOT NULL DEFAULT 0,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 5. DIRECTORS / LEADERSHIP
-- ============================================================
CREATE TABLE directors (
    id             TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name      VARCHAR(120) NOT NULL,
    role           VARCHAR(80)  NOT NULL,
    title          VARCHAR(160) DEFAULT NULL,
    bio_short      TEXT         DEFAULT NULL,
    bio_full       TEXT         DEFAULT NULL,
    photo_path     VARCHAR(255) DEFAULT NULL,
    qualifications VARCHAR(255) DEFAULT NULL,
    linkedin_url   VARCHAR(255) DEFAULT NULL,
    sort_order     TINYINT      NOT NULL DEFAULT 0,
    is_active      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 6. TESTIMONIALS
-- ============================================================
CREATE TABLE testimonials (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(120) NOT NULL,
    company     VARCHAR(120) DEFAULT NULL,
    content     TEXT         NOT NULL,
    rating      TINYINT      NOT NULL DEFAULT 5 CHECK (rating BETWEEN 1 AND 5),
    is_featured TINYINT(1)   NOT NULL DEFAULT 0,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    sort_order  SMALLINT     NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 7. JOB OPENINGS
-- ============================================================
CREATE TABLE job_openings (
    id              SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(120) NOT NULL,
    employment_type ENUM('Full-Time','Part-Time','Internship','Contract') NOT NULL DEFAULT 'Full-Time',
    experience      VARCHAR(80)  DEFAULT NULL,
    qualification   VARCHAR(120) DEFAULT NULL,
    location        VARCHAR(80)  DEFAULT 'Pune',
    description     TEXT         DEFAULT NULL,
    apply_email     VARCHAR(180) NOT NULL DEFAULT 'priyakoli2727@gmail.com',
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    posted_on       DATE         NOT NULL DEFAULT (CURRENT_DATE),
    expires_on      DATE         DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- 8. JOB APPLICATIONS (new table)
-- ============================================================
CREATE TABLE job_applications (
    id              SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id          SMALLINT UNSIGNED NOT NULL,
    applicant_name  VARCHAR(120)      NOT NULL,
    email           VARCHAR(180)      NOT NULL,
    phone           VARCHAR(20)       DEFAULT NULL,
    cover_message   TEXT              DEFAULT NULL,
    resume_path     VARCHAR(255)      DEFAULT NULL,
    status          ENUM('received','shortlisted','rejected','hired') NOT NULL DEFAULT 'received',
    applied_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_application_job
        FOREIGN KEY (job_id) REFERENCES job_openings(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_job    (job_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- 9. LAB GALLERY
-- ============================================================
CREATE TABLE gallery_images (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename    VARCHAR(255) NOT NULL,
    alt_text    VARCHAR(180) NOT NULL,
    label       VARCHAR(80)  DEFAULT NULL,
    section     VARCHAR(60)  DEFAULT 'lab',
    is_wide     TINYINT(1)   NOT NULL DEFAULT 0,
    is_tall     TINYINT(1)   NOT NULL DEFAULT 0,
    sort_order  SMALLINT     NOT NULL DEFAULT 0,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    uploaded_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 10. PROJECTS
-- ============================================================
CREATE TABLE projects (
    id            TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(160) NOT NULL,
    category_tag  VARCHAR(80)  DEFAULT NULL,
    description   TEXT         DEFAULT NULL,
    stat_1_value  VARCHAR(30)  DEFAULT NULL,
    stat_1_label  VARCHAR(40)  DEFAULT NULL,
    stat_2_value  VARCHAR(30)  DEFAULT NULL,
    stat_2_label  VARCHAR(40)  DEFAULT NULL,
    stat_3_value  VARCHAR(30)  DEFAULT NULL,
    stat_3_label  VARCHAR(40)  DEFAULT NULL,
    icon_class    VARCHAR(60)  DEFAULT NULL,
    accent_color  VARCHAR(20)  DEFAULT 'green',
    sort_order    TINYINT      NOT NULL DEFAULT 0,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 11. CLIENT SECTORS
-- ============================================================
CREATE TABLE client_sectors (
    id         TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    icon_class VARCHAR(60)  DEFAULT NULL,
    sort_order TINYINT      NOT NULL DEFAULT 0,
    is_active  TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ============================================================
-- 12. STRENGTHS (Why Choose Us)
-- ============================================================
CREATE TABLE strengths (
    id         TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    heading    VARCHAR(100) NOT NULL,
    body       VARCHAR(255) NOT NULL,
    icon_class VARCHAR(60)  DEFAULT NULL,
    sort_order TINYINT      NOT NULL DEFAULT 0,
    is_active  TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ============================================================
-- 13. INFRASTRUCTURE ITEMS
-- ============================================================
CREATE TABLE infrastructure_items (
    id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(200) NOT NULL,
    icon_class  VARCHAR(60)  DEFAULT NULL,
    sort_order  TINYINT      NOT NULL DEFAULT 0,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ============================================================
-- 14. ADMIN USERS
-- ============================================================
CREATE TABLE admin_users (
    id              TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(60)  NOT NULL UNIQUE,
    email           VARCHAR(180) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    session_token   VARCHAR(64)  DEFAULT NULL,
    role            ENUM('super_admin','admin','viewer') NOT NULL DEFAULT 'admin',
    last_login_at   DATETIME     DEFAULT NULL,
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: username=admin  password=Admin@1234  (CHANGE THIS!)
INSERT INTO admin_users (username, email, password_hash, role) VALUES
  ('admin', 'admin@akankshalab.com',
   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Admin@1234
   'super_admin');

-- ============================================================
-- 15. SITE SETTINGS
-- ============================================================
CREATE TABLE site_settings (
    setting_key   VARCHAR(80)  NOT NULL PRIMARY KEY,
    setting_value TEXT         DEFAULT NULL,
    description   VARCHAR(200) DEFAULT NULL
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Service Categories
INSERT INTO service_categories (name, icon_class, sort_order) VALUES
  ('Environmental Analysis',      'fas fa-leaf',        1),
  ('SPCB Licensing & Compliance', 'fas fa-file-shield', 2);

-- Services
INSERT INTO services (category_id, name, sort_order) VALUES
  (1, 'Ambient Air Monitoring',              1),
  (1, 'Workplace Air Monitoring',            2),
  (1, 'Stack Emission Monitoring',           3),
  (1, 'Noise Monitoring',                    4),
  (1, 'Illumination Monitoring',             5),
  (1, 'Ventilation Survey',                  6),
  (1, 'Industrial / Sewage Effluent',        7),
  (1, 'Drinking Water Test',                 8),
  (1, 'Microbiological Testing for Water',   9),
  (1, 'Sludge & Soil Testing',              10),
  (1, 'Metal Analysis',                     11),
  (2, 'SPCB Consent (CTE & CTO)',            1),
  (2, 'Environmental Impact Assessment',     2),
  (2, 'Pollution Control Compliance',        3),
  (2, 'Environmental Statutory Audit',       4),
  (2, 'Regulatory Authority Liaison',        5);

-- Accreditations
INSERT INTO accreditations
    (title, issuing_body, description, valid_until, certificate_img, badge_icon, sort_order)
VALUES
  ('NABL ISO 17025:2017', 'National Accreditation Board for Testing & Calibration Laboratories',
   'National accreditation for testing laboratories', NULL, NULL, 'fas fa-microscope', 1),
  ('CPCB Recognized', 'Ministry of Environment, Forest and Climate Change',
   'Recognized by MoEF&CC, Govt. of India since 2006', NULL, NULL, 'fas fa-landmark', 2),
  ('ISO 9001:2015', 'Bureau Veritas',
   'Quality Management System', '2026-06-30', 'img/ISO 9001 (1).jpg', 'fas fa-certificate', 3),
  ('ISO 45001:2018', 'Bureau Veritas',
   'Occupational Health & Safety Management System', '2026-10-31', 'img/ISO 45001 (1).jpg', 'fas fa-user-shield', 4);

-- Directors
INSERT INTO directors
    (full_name, role, title, bio_short, qualifications, photo_path, sort_order)
VALUES
  ('Mr. Mahendra Katariya', 'Partner', 'Partner & Strategic Director — 30+ Years of Leadership',
   'Mr. Mahendra Katariya brings over three decades of leadership experience in environmental compliance and laboratory operations.',
   'B.Com., SPCB Compliance Expert, ETP / STP / RO Systems, Regulatory Advisory',
   'img/Mahendra Katariya.jpeg', 1),
  ('Ms. Vaishali Katariya', 'Partner', 'Partner & Operations Director — 20+ Years of Experience',
   'Ms. Vaishali Katariya brings over 20 years of experience in administration, accounts, and operational management.',
   'B.Sc. Computer Science, Administration & Finance, Quality Control Systems, Regulatory Compliance',
   'img/Vaishali Katariya.jpeg', 2);

-- Testimonials
INSERT INTO testimonials (client_name, company, content, is_featured, sort_order)
VALUES
  ('Industrial Client', 'Pune',
   'The team was professional, reports were accurate, and results helped us meet all regulatory deadlines.', 1, 1),
  ('Consultancy Firm', NULL,
   'Reliable and efficient lab with expert staff. We trust Akanksha for all our environmental testing.', 1, 2);

-- Job Openings
INSERT INTO job_openings
    (title, employment_type, experience, qualification, description, posted_on)
VALUES
  ('Senior Analyst', 'Full-Time', '3–5 Years', 'M.Sc. in Chemistry / Environmental Science',
   'Expertise in instrumental analysis (ICP, GC, AAS) and NABL documentation.', CURRENT_DATE),
  ('Field Technician', 'Full-Time', '1–2 Years', 'Diploma/B.Sc.',
   'Responsible for ambient air, stack, and water sampling across client locations.', CURRENT_DATE),
  ('Lab Assistant', 'Internship', 'Freshers Welcome', 'B.Sc. Chemistry / Environmental Science',
   'Assist senior analysts in day-to-day testing of water and wastewater samples. Duration: 6 months.', CURRENT_DATE);

-- Gallery
INSERT INTO gallery_images
    (filename, alt_text, label, is_wide, is_tall, sort_order, section)
VALUES
  ('img/instrumnets room.jpeg',  'Instrument Room',                 'Instrument Room',     1, 1, 1,  'lab'),
  ('img/img1.jpeg',              'Analyst conducting titration',    'Titration Analysis',  0, 0, 2,  'lab'),
  ('img/img3.jpeg',              'Atomic Absorption Spectrophotometer', 'AAS Instrument',  0, 0, 3,  'lab'),
  ('img/img4.jpeg',              'Chemical reagents storage',       'Reagents Storage',    0, 0, 4,  'lab'),
  ('img/img5.jpeg',              'Spectrophotometer analysis',      'Spectrophotometer',   0, 0, 5,  'lab'),
  ('img/balance room.jpeg',      'Balance Room',                    'Balance Room',        0, 0, 6,  'lab'),
  ('img/img6.jpeg',              'Laboratory Overview',             'Lab Overview',        0, 0, 7,  'lab'),
  ('img/weblab.jpeg',            'Wet Lab 1',                       'Wet Lab 1',           0, 0, 8,  'lab'),
  ('img/documents room.jpeg',    'Document Room',                   'Document Room',       0, 0, 9,  'lab'),
  ('img/hot room.jpeg',          'Hot Room',                        'Hot Room',            0, 0, 10, 'lab'),
  ('img/balance room 1.jpeg',    'Balance Room 2',                  'Balance Room 2',      0, 0, 11, 'lab'),
  ('img/building.jpeg',          'Facility Building Exterior',      'Our Facility',        0, 0, 1,  'facility');

-- Strengths
INSERT INTO strengths (heading, body, icon_class, sort_order) VALUES
  ('Accredited Laboratory',    'Accurate and certified lab results for compliance.',        'fas fa-award',           1),
  ('Advanced Technology',      'Cutting-edge instruments for accurate analysis.',           'fas fa-microchip',       2),
  ('Fast & Reliable Results',  'Quick turnaround time with precise data.',                 'fas fa-bolt',            3),
  ('Experienced Professionals','A team of skilled chemists and analysts.',                 'fas fa-microscope',      4),
  ('Customized Solutions',     'Tailored testing packages to meet specific client needs.', 'fas fa-sliders',         5),
  ('Years of Expertise',       'Successfully handling SPCB licensing for industries.',     'fas fa-history',         6),
  ('Hassle-Free Process',      'End-to-end documentation and compliance assistance.',      'fas fa-circle-check',    7),
  ('Regulatory Compliance',    'Ensuring smooth approvals with minimal delays.',           'fas fa-scale-balanced',  8),
  ('Economical Rates',         'Service provided with compatible rates.',                  'fas fa-tag',             9);

-- Infrastructure
INSERT INTO infrastructure_items (description, icon_class, sort_order) VALUES
  ('Advanced instruments for chemical and physical analysis',      'fas fa-microscope',      1),
  ('Field sampling devices for water and air quality',             'fas fa-broadcast-tower', 2),
  ('Stack emission monitoring equipment',                          'fas fa-smog',            3),
  ('Dedicated laboratory for water, wastewater, and air analysis', 'fas fa-vial',            4),
  ('Atomic Absorption Spectrophotometer (AAS)',                    'fas fa-atom',            5),
  ('Wet Lab, Hot Room, Balance Room & Document Room',              'fas fa-flask',           6);

-- Projects
INSERT INTO projects
    (title, category_tag, description,
     stat_1_value, stat_1_label, stat_2_value, stat_2_label, stat_3_value, stat_3_label,
     icon_class, accent_color, sort_order)
VALUES
  ('Water Pollution Control Systems Erection', 'Infrastructure',
   'End-to-end physical installation and setup of advanced filtration and treatment hardware designed for high-capacity industrial use.',
   '100%', 'Compliance', 'ETP', 'Systems', '24/7', 'Support', 'fas fa-tools', 'green', 1),
  ('Commissioning and Management', 'O&M',
   'Expert system startup, performance testing, and O&M (Operation and Maintenance) to ensure long-term regulatory compliance and efficiency.',
   'O&M', 'Managed', 'Long-term', 'Partnership', 'ISO', 'Compliant', 'fas fa-microchip', 'blue', 2);

-- Client Sectors
INSERT INTO client_sectors (name, icon_class, sort_order) VALUES
  ('Manufacturing & Industrial Units', 'fas fa-industry',      1),
  ('Water Treatment Plants',           'fas fa-faucet-drip',   2),
  ('Environmental Agencies',           'fas fa-leaf',          3),
  ('Government & Regulatory Bodies',   'fas fa-balance-scale', 4),
  ('Construction & Infrastructure',    'fas fa-city',          5);

-- Site Settings
INSERT INTO site_settings (setting_key, setting_value, description) VALUES
  ('lab_name',         'Akanksha Analytical and Research Lab',  'Official laboratory name'),
  ('address_line1',    'S. No. 613, Plot No. 5, GangaDham Phase I', 'Address line 1'),
  ('address_line2',    'Bibwewadi, Pune – 411037',              'Address line 2'),
  ('phone',            '020-24240030',                          'Primary phone number'),
  ('email',            'akankshalab2007@gmail.com',               'Primary contact email'),
  ('working_hours',    'Mon – Sat: 9:00 AM – 5:30 PM',         'Office working hours'),
  ('established_year', '2006',                                  'Year MoEF recognition was obtained'),
  ('nabl_accredited',  '1',                                     '1 = yes, 0 = no'),
  ('cpcb_recognized',  '1',                                     '1 = yes, 0 = no');

-- ============================================================
-- END OF SCHEMA
-- ============================================================
