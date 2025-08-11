const express = require('express');
const session = require('express-session');
const bodyParser = require('body-parser');
const path = require('path');
const sqlite3 = require('sqlite3').verbose();
const bcrypt = require('bcryptjs');

const app = express();
const PORT = process.env.PORT || 3001;

// Create SQLite database
const db = new sqlite3.Database('./test_database.db');

// Initialize database tables
function initDatabase() {
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

        // Insert sample data
        insertSampleData();
    });
}

function insertSampleData() {
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
}

// Middleware
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());
app.use(express.static('public'));
app.use(session({
    secret: 'flight-booking-secret-key',
    resave: false,
    saveUninitialized: false,
    cookie: { secure: false, maxAge: 24 * 60 * 60 * 1000 }
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
    
    db.get('SELECT * FROM users WHERE username = ?', [username], (err, user) => {
        if (err) {
            return res.render('login', { message: 'Database error' });
        }
        
        if (!user) {
            return res.render('login', { message: '❌ Không tìm thấy người dùng.' });
        }
        
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
    
    db.get('SELECT * FROM users WHERE username = ?', [username], (err, user) => {
        if (err) {
            return res.render('register', { message: 'Database error' });
        }
        
        if (user) {
            return res.render('register', { message: '❌ Tên đăng nhập đã tồn tại.' });
        }
        
        bcrypt.hash(password, 10, (err, hashedPassword) => {
            if (err) {
                return res.render('register', { message: 'Error creating account' });
            }
            
            db.run('INSERT INTO users (username, password, role) VALUES (?, ?, ?)', 
                [username, hashedPassword, 'user'], (err) => {
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
    
    const searchQuery = `SELECT * FROM chuyen_bay 
                        WHERE diem_di LIKE ? AND diem_den LIKE ? AND ngay_di = ?`;
    
    const airlinesQuery = `SELECT DISTINCT hang_hang_khong FROM chuyen_bay 
                          WHERE diem_di LIKE ? AND diem_den LIKE ? AND ngay_di = ?`;
    
    db.all(searchQuery, [`%${diem_di}%`, `%${diem_den}%`, ngay_di], (err, flights) => {
        if (err) {
            return res.render('showticket', { 
                flights: [], 
                airlines: [], 
                message: 'Database error',
                searchParams: { diem_di, diem_den, ngay_di }
            });
        }
        
        db.all(airlinesQuery, [`%${diem_di}%`, `%${diem_den}%`, ngay_di], (err, airlines) => {
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
    
    db.get('SELECT * FROM chuyen_bay WHERE id = ?', [flightId], (err, flight) => {
        if (err || !flight) {
            return res.redirect('/');
        }
        
        db.all('SELECT ghe_so FROM ve WHERE chuyen_bay_id = ?', [flightId], (err, bookedSeats) => {
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
    
    db.get('SELECT * FROM ve WHERE chuyen_bay_id = ? AND ghe_so = ?', 
        [chuyen_bay_id, ghe_so], (err, existingTicket) => {
        if (err) {
            return res.json({ success: false, message: 'Database error' });
        }
        
        if (existingTicket) {
            return res.json({ success: false, message: `❌ Ghế ${ghe_so} đã có người đặt!` });
        }
        
        db.run(`INSERT INTO ve (ten_nguoi_dat, chuyen_bay_id, ghe_so, danh_xung, sdt, email, cccd) 
                VALUES (?, ?, ?, ?, ?, ?, ?)`, 
            [ten_nguoi_dat, chuyen_bay_id, ghe_so, danh_xung, sdt, email, cccd], function(err) {
            if (err) {
                return res.json({ success: false, message: 'Error booking ticket' });
            }
            
            res.json({ 
                success: true, 
                message: `✅ Đặt vé thành công! Ghế: ${ghe_so}`,
                ticketId: this.lastID
            });
        });
    });
});

app.get('/lichsuve', requireAuth, (req, res) => {
    const query = `
        SELECT v.*, cb.ma_cb, cb.diem_di, cb.diem_den, cb.ngay_di, cb.gio_di, cb.gio_den, cb.hang_hang_khong, cb.gia
        FROM ve v
        JOIN chuyen_bay cb ON v.chuyen_bay_id = cb.id
        ORDER BY v.created_at DESC
    `;
    
    db.all(query, (err, tickets) => {
        if (err) {
            tickets = [];
        }
        
        res.render('lichsuve', { tickets, user: req.session.user });
    });
});

// Admin routes
app.get('/admin', requireAdmin, (req, res) => {
    const statsQuery = `
        SELECT 
            (SELECT COUNT(*) FROM chuyen_bay) as total_flights,
            (SELECT COUNT(*) FROM ve) as total_tickets,
            (SELECT COUNT(*) FROM users) as total_users,
            (SELECT SUM(cb.gia) FROM ve v JOIN chuyen_bay cb ON v.chuyen_bay_id = cb.id WHERE v.trang_thai_thanh_toan = 1) as total_revenue
    `;
    
    db.get(statsQuery, (err, stats) => {
        if (err) {
            stats = { total_flights: 0, total_tickets: 0, total_users: 0, total_revenue: 0 };
        }
        
        res.render('admin/admin', { user: req.session.user, stats });
    });
});

app.get('/admin/sanphamadmin', requireAdmin, (req, res) => {
    db.all('SELECT * FROM chuyen_bay ORDER BY ngay_di DESC', (err, flights) => {
        if (err) {
            flights = [];
        }
        
        res.render('admin/sanphamadmin', { flights, user: req.session.user });
    });
});

app.post('/admin/add-flight', requireAdmin, (req, res) => {
    const { ma_cb, diem_di, diem_den, ngay_di, gio_di, gio_den, hang_hang_khong, loai_may_bay, gia, tong_ghe } = req.body;
    
    db.run(`INSERT INTO chuyen_bay (ma_cb, diem_di, diem_den, ngay_di, gio_di, gio_den, hang_hang_khong, loai_may_bay, gia, tong_ghe) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`, 
        [ma_cb, diem_di, diem_den, ngay_di, gio_di, gio_den, hang_hang_khong, loai_may_bay, gia, tong_ghe || 60], 
        function(err) {
            if (err) {
                return res.json({ success: false, message: 'Error adding flight' });
            }
            
            res.json({ success: true, message: 'Flight added successfully' });
        });
});

app.delete('/admin/delete-flight/:id', requireAdmin, (req, res) => {
    const flightId = req.params.id;
    
    db.run('DELETE FROM ve WHERE chuyen_bay_id = ?', [flightId], (err) => {
        if (err) {
            return res.json({ success: false, message: 'Error deleting flight' });
        }
        
        db.run('DELETE FROM chuyen_bay WHERE id = ?', [flightId], (err) => {
            if (err) {
                return res.json({ success: false, message: 'Error deleting flight' });
            }
            
            res.json({ success: true, message: 'Flight deleted successfully' });
        });
    });
});

// Initialize database and start server
initDatabase();

app.listen(PORT, () => {
    console.log(`🚀 Test Server running on http://localhost:${PORT}`);
    console.log(`📊 Admin login: username=admin, password=admin123`);
    console.log(`💾 Using SQLite database: ./test_database.db`);
});