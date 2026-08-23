-- ============================================================
-- PG RECOMMENDATION SYSTEM - FINAL V1 SCHEMA
-- Stack: PHP (PDO) + MySQL
-- Tables are created in strict dependency order so this file
-- can be run top-to-bottom with zero FK errors.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS inquiries;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS user_preference_locations;
DROP TABLE IF EXISTS user_preference_amenities;
DROP TABLE IF EXISTS user_preferences;
DROP TABLE IF EXISTS charges;
DROP TABLE IF EXISTS pg_images;
DROP TABLE IF EXISTS pg_amenities;
DROP TABLE IF EXISTS amenities;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS pgs;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- 1. USERS
-- Holds normal users, PG owners, and admins (role column).
-- ------------------------------------------------------------
CREATE TABLE users (
    user_id  INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(100) NOT NULL,
    email  VARCHAR(100) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    role  ENUM('user','owner','admin') NOT NULL DEFAULT 'user',
    contact_number  VARCHAR(20) NOT NULL UNIQUE,
    gender ENUM('male','female','other') DEFAULT NULL, 
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ------------------------------------------------------------
-- 2. LOCATIONS
-- A standardized, selectable locality (state -> city -> area),
-- NOT an individual PG's exact address. lat/long here is the
-- locality's general coordinate, used to seed a dropdown/autocomplete
-- and for coarse preference matching - not for distance-to-PG maths.
-- ------------------------------------------------------------

CREATE TABLE locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    state VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    area VARCHAR(100) NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_location (state, city, area)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. PGS
-- Central listing table. Carries BOTH location_id (which
-- locality, for filtering/dropdowns) AND its own geocoded
-- latitude/longitude (exact point, for distance calculation).
-- ------------------------------------------------------------
CREATE TABLE pgs (
    pg_id                   INT AUTO_INCREMENT PRIMARY KEY,
    owner_id                INT NOT NULL,
    location_id             INT NOT NULL,
    pg_name                 VARCHAR(150) NOT NULL,
    address                 TEXT NOT NULL,
    latitude                DECIMAL(10,8) NOT NULL,   -- stored via geocoding of `address`
    longitude               DECIMAL(11,8) NOT NULL,   -- stored via geocoding of `address`
    description             TEXT NULL,
    gender_preference       ENUM('male','female','co-ed') NOT NULL DEFAULT 'co-ed',
    rules                   TEXT NULL,
    notice_period_days      INT NULL,
    lock_in_period_months   INT NULL,
    verification_status     ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    listing_status          ENUM('draft','active','inactive') NOT NULL DEFAULT 'draft',
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pgs_owner FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    CONSTRAINT fk_pgs_location FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. ROOMS
-- Pricing + occupancy lives here, per room offering. Security
-- deposit is here (not on `pgs`) because deposit can differ by
-- room type in a room-centric pricing model.
-- available_beds (capacity - occupied_beds) is CALCULATED, not stored.
-- ------------------------------------------------------------
CREATE TABLE rooms (
    room_id             INT AUTO_INCREMENT PRIMARY KEY,
    pg_id                INT NOT NULL,
    room_type            ENUM('single','double','triple','multi_sharing') NOT NULL,
    capacity             TINYINT UNSIGNED NOT NULL,
    occupied_beds        TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rent                 DECIMAL(10,2) NOT NULL,
    security_deposit     DECIMAL(10,2) NULL,
    bathroom_type        ENUM('attached','shared','none') NOT NULL DEFAULT 'shared',
    description           TEXT NULL,
    window_ventilation   BOOLEAN NOT NULL DEFAULT TRUE,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_rooms_pg FOREIGN KEY (pg_id) REFERENCES pgs(pg_id) ON DELETE CASCADE,
    CONSTRAINT chk_occupied_le_capacity CHECK (occupied_beds <= capacity)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. AMENITIES (master list)
-- ------------------------------------------------------------
CREATE TABLE amenities (
    amenity_id      INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL UNIQUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. PG_AMENITIES (junction: PG <-> Amenities)
-- included_in_rent = "is this amenity's cost bundled into rent,
-- or billed separately via `charges`?" - NOT "does the PG have it".
-- ------------------------------------------------------------
CREATE TABLE pg_amenities (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    pg_id                INT NOT NULL,
    amenity_id           INT NOT NULL,
    included_in_rent     BOOLEAN NOT NULL DEFAULT TRUE,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pgam_pg FOREIGN KEY (pg_id) REFERENCES pgs(pg_id) ON DELETE CASCADE,
    CONSTRAINT fk_pgam_amenity FOREIGN KEY (amenity_id) REFERENCES amenities(amenity_id) ON DELETE CASCADE,
    UNIQUE KEY uq_pg_amenity (pg_id, amenity_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. PG_IMAGES
-- ------------------------------------------------------------
CREATE TABLE pg_images (
    image_id                INT AUTO_INCREMENT PRIMARY KEY,
    pg_id                    INT NOT NULL,
    image_path               VARCHAR(500) NOT NULL,
    image_type               VARCHAR(50) NULL,        -- e.g. 'exterior','room','bathroom','kitchen'
    verification_status      ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    created_at                TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at                TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_images_pg FOREIGN KEY (pg_id) REFERENCES pgs(pg_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. CHARGES
-- Recurring/extra costs not bundled into rent (food, electricity,
-- laundry...). Security deposit is intentionally NOT here - it
-- lives on `rooms` since it's part of the room offering, not a
-- recurring monthly cost.
-- ------------------------------------------------------------
CREATE TABLE charges (
    charge_id       INT AUTO_INCREMENT PRIMARY KEY,
    pg_id           INT NOT NULL,
    charge_name     VARCHAR(100) NOT NULL,     -- 'Food','Electricity','Laundry','Maintenance'...
    amount          DECIMAL(10,2) NOT NULL,
    charge_basis    ENUM('fixed_monthly','per_unit') NOT NULL DEFAULT 'fixed_monthly',
    unit            VARCHAR(30) NULL,           -- e.g. 'kWh' when charge_basis = 'per_unit'
    description     TEXT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_charges_pg FOREIGN KEY (pg_id) REFERENCES pgs(pg_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 9. USER_PREFERENCES
-- ONE saved/persistent preference profile per user (drives the
-- recommendation engine) - not a log of every search performed.
-- No lat/long and no preferred_location_id here on purpose:
-- preferred localities go through user_preference_locations.
-- ------------------------------------------------------------
CREATE TABLE user_preferences (
    preference_id       INT AUTO_INCREMENT PRIMARY KEY,
    user_id              INT NOT NULL UNIQUE,
    min_budget            DECIMAL(10,2) NULL,
    max_budget            DECIMAL(10,2) NULL,
    room_type             ENUM('single','double','triple','multi_sharing') NULL,
    gender_preference     ENUM('male','female','co-ed','no_preference') NULL,
    max_distance          DECIMAL(6,2) NULL,     -- in km, compared against calculated distance
    created_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_prefs_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 10. USER_PREFERENCE_AMENITIES (junction)
-- preference_type distinguishes must_have (hard filter) from
-- preferred (contributes to match score) - this is what the
-- recommendation engine reads.
-- ------------------------------------------------------------
CREATE TABLE user_preference_amenities (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    preference_id     INT NOT NULL,
    amenity_id        INT NOT NULL,
    preference_type   ENUM('must_have','preferred') NOT NULL DEFAULT 'preferred',
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_prefam_preference FOREIGN KEY (preference_id) REFERENCES user_preferences(preference_id) ON DELETE CASCADE,
    CONSTRAINT fk_prefam_amenity FOREIGN KEY (amenity_id) REFERENCES amenities(amenity_id) ON DELETE CASCADE,
    UNIQUE KEY uq_preference_amenity (preference_id, amenity_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 11. USER_PREFERENCE_LOCATIONS (junction)
-- A preference can point at multiple localities (Rohini,
-- Nangloi, Rajiv Chowk...) - many-to-many with locations.
-- ------------------------------------------------------------
CREATE TABLE user_preference_locations (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    preference_id     INT NOT NULL,
    location_id        INT NOT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_prefloc_preference FOREIGN KEY (preference_id) REFERENCES user_preferences(preference_id) ON DELETE CASCADE,
    CONSTRAINT fk_prefloc_location FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE CASCADE,
    UNIQUE KEY uq_preference_location (preference_id, location_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 12. FAVORITES (User <-> PG, feeds the "compare" feature)
-- ------------------------------------------------------------
CREATE TABLE favorites (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    pg_id         INT NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_fav_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_fav_pg FOREIGN KEY (pg_id) REFERENCES pgs(pg_id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_pg_favorite (user_id, pg_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 13. REVIEWS
-- One user -> one review per PG (UNIQUE). Editing updates the
-- same row instead of inserting a new one.
-- ------------------------------------------------------------
CREATE TABLE reviews (
    review_id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id                INT NOT NULL,
    pg_id                  INT NOT NULL,
    rating                 TINYINT UNSIGNED NOT NULL,
    review_text             TEXT NULL,
    verification_status     ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    created_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_pg FOREIGN KEY (pg_id) REFERENCES pgs(pg_id) ON DELETE CASCADE,
    CONSTRAINT chk_rating_range CHECK (rating BETWEEN 1 AND 5),
    UNIQUE KEY uq_user_pg_review (user_id, pg_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 14. INQUIRIES
-- No unique(user_id, pg_id) - a user may contact the same PG
-- more than once, unlike favorites/reviews.
-- ------------------------------------------------------------
CREATE TABLE inquiries (
    inquiry_id       INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT NOT NULL,
    pg_id             INT NOT NULL,
    message            TEXT NOT NULL,
    status             ENUM('pending','responded','closed') NOT NULL DEFAULT 'pending',
    inquiry_date        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_inquiries_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_inquiries_pg FOREIGN KEY (pg_id) REFERENCES pgs(pg_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- HELPFUL INDEXES
-- ============================================================
CREATE INDEX idx_pgs_location ON pgs(location_id);
CREATE INDEX idx_pgs_listing_status ON pgs(listing_status);
CREATE INDEX idx_pgs_gender ON pgs(gender_preference);
CREATE INDEX idx_rooms_pg ON rooms(pg_id);
CREATE INDEX idx_rooms_rent ON rooms(rent);
CREATE INDEX idx_charges_pg ON charges(pg_id);
CREATE INDEX idx_inquiries_pg ON inquiries(pg_id);
CREATE INDEX idx_reviews_pg ON reviews(pg_id);

-- ============================================================
-- SEED DATA: amenities master list
-- ============================================================
INSERT INTO amenities (name) VALUES
    ('WiFi'),
    ('Food'),
    ('Laundry'),
    ('Parking'),
    ('AC'),
    ('Lift'),
    ('Power Backup'),
    ('Housekeeping'),
    ('Security'),
    ('Washing Machine'),
    ('Refrigerator'),
    ('Study Table'),
    ('CCTV'),
    ('Geyser'),
    ('TV');

-- ============================================================
-- SAMPLE QUERIES (calculated values - not stored anywhere)
-- ============================================================

-- Available beds per room (capacity - occupied_beds)
-- SELECT room_id, pg_id, room_type, (capacity - occupied_beds) AS available_beds
-- FROM rooms;

-- Estimated monthly cost = rent + fixed_monthly charges for that PG
-- SELECT r.room_id, r.pg_id, r.rent,
--        r.rent + IFNULL(SUM(CASE WHEN c.charge_basis = 'fixed_monthly' THEN c.amount ELSE 0 END), 0)
--            AS estimated_monthly_cost
-- FROM rooms r
-- LEFT JOIN charges c ON c.pg_id = r.pg_id
-- GROUP BY r.room_id;

-- Distance between a user's chosen point and a PG (Haversine, km)
-- SELECT pg_id, pg_name,
--   (6371 * ACOS(
--       COS(RADIANS(:user_lat)) * COS(RADIANS(latitude)) *
--       COS(RADIANS(longitude) - RADIANS(:user_lng)) +
--       SIN(RADIANS(:user_lat)) * SIN(RADIANS(latitude))
--   )) AS distance_km
-- FROM pgs
-- HAVING distance_km <= :max_distance
-- ORDER BY distance_km;
