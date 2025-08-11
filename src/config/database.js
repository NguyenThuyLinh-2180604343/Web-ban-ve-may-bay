const mysql = require('mysql2');
const sqlite3 = require('sqlite3').verbose();

// Database configuration
const dbConfig = {
    mysql: {
        host: process.env.DB_HOST || 'webvemaybay-db.cv2g6kio2mny.ap-southeast-1.rds.amazonaws.com',
        user: process.env.DB_USER || 'admin',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'vemaybay'
    },
    sqlite: {
        filename: process.env.SQLITE_DB || './test_database.db'
    }
};

// Create MySQL connection
const createMySQLConnection = () => {
    return mysql.createConnection(dbConfig.mysql);
};

// Create SQLite connection
const createSQLiteConnection = () => {
    return new sqlite3.Database(dbConfig.sqlite.filename);
};

// Initialize database tables for SQLite
const initializeSQLiteTables = (db) => {
    db.serialize(() => {
        // Create users table
        db.run(`CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE,
            password TEXT,
            role TEXT DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )`);

        // Create flights table
        db.run(`CREATE TABLE IF NOT EXISTS chuyen_bay (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ma_cb TEXT,
            diem_di TEXT,
            diem_den TEXT,
            ngay_di DATE,
            gio_di TIME,
            gio_den TIME,
            hang_hang_khong TEXT,
            loai_may_bay TEXT,
            gia DECIMAL(10,2),
            tong_ghe INTEGER DEFAULT 60
        )`);

        // Create tickets table
        db.run(`CREATE TABLE IF NOT EXISTS ve (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ten_nguoi_dat TEXT,
            chuyen_bay_id INTEGER,
            ghe_so TEXT,
            trang_thai_thanh_toan INTEGER DEFAULT 0,
            danh_xung TEXT,
            sdt TEXT,
            email TEXT,
            cccd TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (chuyen_bay_id) REFERENCES chuyen_bay(id)
        )`);
    });
};

module.exports = {
    dbConfig,
    createMySQLConnection,
    createSQLiteConnection,
    initializeSQLiteTables
};