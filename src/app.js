const express = require('express');
const session = require('express-session');
const bodyParser = require('body-parser');
const path = require('path');
const bcrypt = require('bcryptjs');

// Import configurations and utilities
const { createSQLiteConnection, initializeSQLiteTables } = require('./config/database');
const { router, initializeRoutes } = require('./routes');

const app = express();
const PORT = process.env.PORT || 3002;

// Create database connection (using SQLite for development)
const db = createSQLiteConnection();

// Initialize database tables and sample data
initializeSQLiteTables(db);

// Insert sample data
const insertSampleData = () => {
    // Insert sample admin user
    const adminPassword = bcrypt.hashSync('admin123', 10);
    db.run(`INSERT OR IGNORE INTO users (username, password, role) VALUES (?, ?, ?)`, 
        ['admin', adminPassword, 'admin']);

    // Insert sample flights
    const sampleFlights = [
        ['VN101', 'Hà Nội', 'TP.HCM', '2024-02-15', '08:00', '10:30', 'Vietnam Airlines', 'Boeing 787', 2500000, 180],
        ['VJ202', 'TP.HCM', 'Đà Nẵng', '2024-02-16', '14:00', '15:30', 'VietJet Air', 'Airbus A320', 1200000, 150],
        ['BL303', 'Hà Nội', 'Nha Trang', '2024-02-17', '09:30', '12:00', 'Bamboo Airways', 'Boeing 737', 1800000, 160],
        ['JQ404', 'TP.HCM', 'Phú Quốc', '2024-02-18', '16:00', '17:15', 'Jetstar', 'Airbus A320', 1500000, 140]
    ];

    sampleFlights.forEach(flight => {
        db.run(`INSERT OR IGNORE INTO chuyen_bay 
                (ma_cb, diem_di, diem_den, ngay_di, gio_di, gio_den, hang_hang_khong, loai_may_bay, gia, tong_ghe) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`, flight);
    });
};

// Insert sample data
insertSampleData();

// Middleware
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());
app.use(express.static(path.join(__dirname, '../public')));
app.use(session({
    secret: process.env.SESSION_SECRET || 'flight-booking-secret-key',
    resave: false,
    saveUninitialized: false,
    cookie: { secure: false, maxAge: 24 * 60 * 60 * 1000 } // 24 hours
}));

// Set view engine
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, '../views'));

// Initialize routes with database connection
app.use('/', initializeRoutes(db));

// Error handling middleware
app.use((err, req, res, next) => {
    console.error(err.stack);
    res.status(500).send('Something broke!');
});

// 404 handler
app.use((req, res) => {
    res.status(404).render('404', { message: 'Page not found' });
});

// Start server with error handling
const server = app.listen(PORT, () => {
    console.log(`🚀 Server is running on http://localhost:${PORT}`);
    console.log(`📊 Admin login: username=admin, password=admin123`);
    console.log(`💾 Using SQLite database: ./test_database.db`);
    console.log(`📁 Project structure: MVC pattern`);
});

// Handle port conflicts
server.on('error', (err) => {
    if (err.code === 'EADDRINUSE') {
        console.log(`❌ Port ${PORT} is already in use`);
        console.log(`💡 Try running: lsof -ti:${PORT} | xargs kill -9`);
        console.log(`🔄 Or change PORT in .env file`);
        process.exit(1);
    } else {
        console.error('Server error:', err);
    }
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 SIGTERM received, shutting down gracefully');
    server.close(() => {
        console.log('✅ Process terminated');
        db.close();
    });
});

module.exports = app;