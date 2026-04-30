PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS stations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    station_code TEXT NOT NULL,
    province TEXT,
    municipality TEXT,
    locality TEXT,
    postal_code TEXT,
    address TEXT,
    margin TEXT,
    longitude NUMERIC,
    latitude NUMERIC,
    brand TEXT,
    sale_type TEXT,
    provider TEXT,
    hours TEXT,
    service_type TEXT,
    municipality_code TEXT,
    province_code TEXT,
    autonomous_community_code TEXT,
    created_at DATETIME,
    updated_at DATETIME
);

CREATE UNIQUE INDEX IF NOT EXISTS stations_station_code_unique ON stations (station_code);
CREATE INDEX IF NOT EXISTS stations_province_index ON stations (province);
CREATE INDEX IF NOT EXISTS stations_municipality_index ON stations (municipality);
CREATE INDEX IF NOT EXISTS stations_brand_index ON stations (brand);

CREATE TABLE IF NOT EXISTS prices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    station_id INTEGER NOT NULL,
    collected_at DATETIME NOT NULL,
    gas95_e5 NUMERIC,
    gas95_e10 NUMERIC,
    gas95_e25 NUMERIC,
    gas95_e5_premium NUMERIC,
    gas95_e85 NUMERIC,
    gas98_e5 NUMERIC,
    gas98_e10 NUMERIC,
    renewable_gasoline NUMERIC,
    diesel_a NUMERIC,
    diesel_b NUMERIC,
    diesel_premium NUMERIC,
    renewable_diesel NUMERIC,
    bioethanol NUMERIC,
    pct_bio NUMERIC,
    biodiesel NUMERIC,
    pct_ester NUMERIC,
    glp NUMERIC,
    gnc NUMERIC,
    gnl NUMERIC,
    biogas_cng NUMERIC,
    biogas_lng NUMERIC,
    hydrogen NUMERIC,
    adblue NUMERIC,
    ammonia NUMERIC,
    methanol NUMERIC,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (station_id) REFERENCES stations(id) ON DELETE CASCADE
);

CREATE UNIQUE INDEX IF NOT EXISTS prices_station_collected_unique ON prices (station_id, collected_at);
CREATE INDEX IF NOT EXISTS prices_collected_at_index ON prices (collected_at);
CREATE INDEX IF NOT EXISTS prices_station_id_index ON prices (station_id);
