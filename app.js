const express = require('express');
const session = require('express-session');
const bodyParser = require('body-parser');
const path = require('path');
const mysql = require('mysql2');
const bcrypt = require('bcryptjs');

const app = express();
const PORT = process.env.PORT || 3001;

// Database configuration
const dbConfig = {
    host: 'webvemaybay-db.cv2g6kio2mny.ap-southeast-1.rds.amazonaws.com',
    user: 'admin',
    password: '', // Add your RDS password here
    database: 'vemaybay'
};

// Create database connection
const db = mysql.createConnection(dbConfig);

// Connect to database
db.connect((err) => {
    if (err) {
        console.error('Database connection failed:', err);
        return;
    }
    console.log('Connected to MySQL database');

    // Create tables if they don't exist
    createTables();
});

// Middleware
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());
app.use(express.static('public'));
app.use(session({
    secret: 'flight-booking-secret-key',
    resave: false,
    saveUninitialized: false,
    cookie: { secure: false, maxAge: 24 * 60 * 60 * 1000 } // 24 hours
}));

// Set view engine
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Authentication middleware
const requireAuth = (req, res, next) => {
    if (req.session.user) {
        next();
    } else {
        res.redirect('/login');
    }
};

const requireAdmin = (req, res, next) => {
    if (req.session.user && req.session.user.role === 'admin') {
        next();
    } else {
        res.status(403).send('Access denied');
    }
};

// Routes
app.get('/', requireAuth, (req, res) => {
    res.render('index', { user: req.session.user });
});

app.get('/login', (req, res) => {
    res.render('login', { message: '' });
});

app.post('/login', (req, res) => {
    const { username, password } = req.body;

    db.query('SELECT * FROM users WHERE username = ?', [username], (err, results) => {
        if (err) {
            return res.render('login', { message: 'Database error' });
        }

        if (results.length === 0) {
            return res.render('login', { message: '❌ Không tìm thấy người dùng.' });
        }

        const user = results[0];
        bcrypt.compare(password, user.password, (err, isMatch) => {
            if (err) {
                return res.render('login', { message: 'Authentication error' });
            }

            if (isMatch) {
                req.session.user = {
                    id: user.id,
                    username: user.username,
                    role: user.role
                };
                res.redirect('/');
            } else {
                res.render('login', { message: '❌ Sai mật khẩu.' });
            }
        });
    });
});

app.get('/register', (req, res) => {
    res.render('register', { message: '' });
});

app.post('/register', (req, res) => {
    const { username, password } = req.body;

    // Check if user already exists
    db.query('SELECT * FROM users WHERE username = ?', [username], (err, results) => {
        if (err) {
            return res.render('register', { message: 'Database error' });
        }

        if (results.length > 0) {
            return res.render('register', { message: '❌ Tên đăng nhập đã tồn tại.' });
        }

        // Hash password and create user
        bcrypt.hash(password, 10, (err, hashedPassword) => {
            if (err) {
                return res.render('register', { message: 'Error creating account' });
            }

            db.query('INSERT INTO users (username, password, role) VALUES (?, ?, ?)',
                [username, hashedPassword, 'user'], (err, result) => {
                    if (err) {
                        return res.render('register', { message: 'Error creating account' });
                    }

                    res.render('register', { message: '✅ Đăng ký thành công! Hãy đăng nhập.' });
                });
        });
    });
});

app.get('/logout', (req, res) => {
    req.session.destroy();
    res.redirect('/login');
});

app.get('/showticket', requireAuth, (req, res) => {
    const { diem_di, diem_den, ngay_di } = req.query;

    if (!diem_di || !diem_den || !ngay_di) {
        return res.render('showticket', {
            flights: [],
            airlines: [],
            message: '⚠️ Vui lòng nhập đầy đủ thông tin tìm kiếm.',
            searchParams: { diem_di, diem_den, ngay_di }
        });
    }

    const searchQuery = `
        SELECT * FROM chuyen_bay 
        WHERE diem_di LIKE ? AND diem_den LIKE ? AND ngay_di = ?
    `;

    const airlinesQuery = `
        SELECT DISTINCT hang_hang_khong FROM chuyen_bay 
        WHERE diem_di LIKE ? AND diem_den LIKE ? AND ngay_di = ?
    `;

    db.query(searchQuery, [`%${diem_di}%`, `%${diem_den}%`, ngay_di], (err, flights) => {
        if (err) {
            return res.render('showticket', {
                flights: [],
                airlines: [],
                message: 'Database error',
                searchParams: { diem_di, diem_den, ngay_di }
            });
        }

        db.query(airlinesQuery, [`%${diem_di}%`, `%${diem_den}%`, ngay_di], (err, airlines) => {
            if (err) {
                airlines = [];
            }

            const message = flights.length === 0 ? '❌ Không tìm thấy chuyến bay phù hợp.' : '';

            res.render('showticket', {
                flights,
                airlines,
                message,
                searchParams: { diem_di, diem_den, ngay_di }
            });
        });
    });
});

app.get('/chitiet/:id', requireAuth, (req, res) => {
    const flightId = req.params.id;

    db.query('SELECT * FROM chuyen_bay WHERE id = ?', [flightId], (err, results) => {
        if (err || results.length === 0) {
            return res.redirect('/');
        }

        const flight = results[0];

        // Get booked seats
        db.query('SELECT ghe_so FROM ve WHERE chuyen_bay_id = ?', [flightId], (err, bookedSeats) => {
            if (err) {
                bookedSeats = [];
            }

            const bookedSeatNumbers = bookedSeats.map(seat => seat.ghe_so);

            res.render('chitiet', {
                flight,
                bookedSeats: bookedSeatNumbers,
                user: req.session.user
            });
        });
    });
});

app.post('/dat-ve', requireAuth, (req, res) => {
    const { chuyen_bay_id, ten_nguoi_dat, ghe_so, danh_xung, sdt, email, cccd } = req.body;

    // Check if seat is already booked
    db.query('SELECT * FROM ve WHERE chuyen_bay_id = ? AND ghe_so = ?',
        [chuyen_bay_id, ghe_so], (err, results) => {
            if (err) {
                return res.json({ success: false, message: 'Database error' });
            }

            if (results.length > 0) {
                return res.json({ success: false, message: `❌ Ghế ${ghe_so} đã có người đặt!` });
            }

            // Book the seat
            db.query(`INSERT INTO ve (ten_nguoi_dat, chuyen_bay_id, ghe_so, danh_xung, sdt, email, cccd) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)`,
                [ten_nguoi_dat, chuyen_bay_id, ghe_so, danh_xung, sdt, email, cccd], (err, result) => {
                    if (err) {
                        return res.json({ success: false, message: 'Error booking ticket' });
                    }

                    // TODO: Send SNS notification here if needed

                    res.json({
                        success: true,
                        message: `✅ Đặt vé thành công! Ghế: ${ghe_so}`,
                        ticketId: result.insertId
                    });
                });
        });
});

app.get('/lichsuve', requireAuth, (req, res) => {
    const userId = req.session.user.id;

    const query = `
        SELECT v.*, cb.ma_cb, cb.diem_di, cb.diem_den, cb.ngay_di, cb.gio_di, cb.gio_den, cb.hang_hang_khong, cb.gia
        FROM ve v
        JOIN chuyen_bay cb ON v.chuyen_bay_id = cb.id
        ORDER BY v.created_at DESC
    `;

    db.query(query, (err, tickets) => {
        if (err) {
            tickets = [];
        }

        res.render('lichsuve', { tickets, user: req.session.user });
    });
});

// Admin routes
app.get('/admin', requireAdmin, (req, res) => {
    // Get statistics
    const statsQuery = `
        SELECT 
            (SELECT COUNT(*) FROM chuyen_bay) as total_flights,
            (SELECT COUNT(*) FROM ve) as total_tickets,
            (SELECT COUNT(*) FROM users) as total_users,
            (SELECT SUM(cb.gia) FROM ve v JOIN chuyen_bay cb ON v.chuyen_bay_id = cb.id WHERE v.trang_thai_thanh_toan = 1) as total_revenue
    `;

    db.query(statsQuery, (err, stats) => {
        if (err) {
            stats = [{ total_flights: 0, total_tickets: 0, total_users: 0, total_revenue: 0 }];
        }

        res.render('admin/admin', { user: req.session.user, stats: stats[0] });
    });
});

app.get('/admin/sanphamadmin', requireAdmin, (req, res) => {
    db.query('SELECT * FROM chuyen_bay ORDER BY ngay_di DESC', (err, flights) => {
        if (err) {
            flights = [];
        }

        res.render('admin/sanphamadmin', { flights, user: req.session.user });
    });
});

app.post('/admin/add-flight', requireAdmin, (req, res) => {
    const { ma_cb, diem_di, diem_den, ngay_di, gio_di, gio_den, hang_hang_khong, loai_may_bay, gia, tong_ghe } = req.body;

    const query = `INSERT INTO chuyen_bay (ma_cb, diem_di, diem_den, ngay_di, gio_di, gio_den, hang_hang_khong, loai_may_bay, gia, tong_ghe) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`;

    db.query(query, [ma_cb, diem_di, diem_den, ngay_di, gio_di, gio_den, hang_hang_khong, loai_may_bay, gia, tong_ghe || 60], (err, result) => {
        if (err) {
            return res.json({ success: false, message: 'Error adding flight' });
        }

        res.json({ success: true, message: 'Flight added successfully' });
    });
});

app.delete('/admin/delete-flight/:id', requireAdmin, (req, res) => {
    const flightId = req.params.id;

    // First delete related tickets
    db.query('DELETE FROM ve WHERE chuyen_bay_id = ?', [flightId], (err) => {
        if (err) {
            return res.json({ success: false, message: 'Error deleting flight' });
        }

        // Then delete the flight
        db.query('DELETE FROM chuyen_bay WHERE id = ?', [flightId], (err) => {
            if (err) {
                return res.json({ success: false, message: 'Error deleting flight' });
            }

            res.json({ success: true, message: 'Flight deleted successfully' });
        });
    });
});

// Function to create tables
function createTables() {
    const createFlightsTable = `
        CREATE TABLE IF NOT EXISTS chuyen_bay (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ma_cb VARCHAR(20),
            diem_di VARCHAR(100),
            diem_den VARCHAR(100),
            ngay_di DATE,
            gio_di TIME,
            gio_den TIME,
            hang_hang_khong VARCHAR(100),
            loai_may_bay VARCHAR(100),
            gia DECIMAL(10,2),
            tong_ghe INT DEFAULT 60
        )
    `;

    const createTicketsTable = `
        CREATE TABLE IF NOT EXISTS ve (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ten_nguoi_dat VARCHAR(100),
            chuyen_bay_id INT,
            ghe_so VARCHAR(10),
            trang_thai_thanh_toan TINYINT DEFAULT 0,
            danh_xung VARCHAR(20),
            sdt VARCHAR(20),
            email VARCHAR(100),
            cccd VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (chuyen_bay_id) REFERENCES chuyen_bay(id)
        )
    `;

    const createUsersTable = `
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE,
            password VARCHAR(255),
            role VARCHAR(20) DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    `;

    db.query(createFlightsTable, (err) => {
        if (err) console.error('Error creating flights table:', err);
    });

    db.query(createTicketsTable, (err) => {
        if (err) console.error('Error creating tickets table:', err);
    });

    db.query(createUsersTable, (err) => {
        if (err) console.error('Error creating users table:', err);
    });
}



app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});