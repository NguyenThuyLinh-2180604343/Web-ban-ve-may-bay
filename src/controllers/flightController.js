class FlightController {
    constructor(db) {
        this.db = db;
    }

    // Show home page
    showHome(req, res) {
        res.render('index', { user: req.session.user });
    }

    // Search flights
    searchFlights(req, res) {
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
        
        // For SQLite
        if (this.db.all) {
            this.db.all(searchQuery, [`%${diem_di}%`, `%${diem_den}%`, ngay_di], (err, flights) => {
                if (err) {
                    return res.render('showticket', { 
                        flights: [], 
                        airlines: [], 
                        message: 'Database error',
                        searchParams: { diem_di, diem_den, ngay_di }
                    });
                }
                
                this.db.all(airlinesQuery, [`%${diem_di}%`, `%${diem_den}%`, ngay_di], (err, airlines) => {
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
        } else {
            // For MySQL
            this.db.query(searchQuery, [`%${diem_di}%`, `%${diem_den}%`, ngay_di], (err, flights) => {
                if (err) {
                    return res.render('showticket', { 
                        flights: [], 
                        airlines: [], 
                        message: 'Database error',
                        searchParams: { diem_di, diem_den, ngay_di }
                    });
                }
                
                this.db.query(airlinesQuery, [`%${diem_di}%`, `%${diem_den}%`, ngay_di], (err, airlines) => {
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
        }
    }

    // Show flight details
    showFlightDetails(req, res) {
        const flightId = req.params.id;
        
        // For SQLite
        if (this.db.get) {
            this.db.get('SELECT * FROM chuyen_bay WHERE id = ?', [flightId], (err, flight) => {
                if (err || !flight) {
                    return res.redirect('/');
                }
                
                this.db.all('SELECT ghe_so FROM ve WHERE chuyen_bay_id = ?', [flightId], (err, bookedSeats) => {
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
        } else {
            // For MySQL
            this.db.query('SELECT * FROM chuyen_bay WHERE id = ?', [flightId], (err, results) => {
                if (err || results.length === 0) {
                    return res.redirect('/');
                }
                
                const flight = results[0];
                
                this.db.query('SELECT ghe_so FROM ve WHERE chuyen_bay_id = ?', [flightId], (err, bookedSeats) => {
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
        }
    }

    // Book ticket
    bookTicket(req, res) {
        const { chuyen_bay_id, ten_nguoi_dat, ghe_so, danh_xung, sdt, email, cccd } = req.body;
        
        // For SQLite
        if (this.db.get) {
            this.db.get('SELECT * FROM ve WHERE chuyen_bay_id = ? AND ghe_so = ?', 
                [chuyen_bay_id, ghe_so], (err, existingTicket) => {
                if (err) {
                    return res.json({ success: false, message: 'Database error' });
                }
                
                if (existingTicket) {
                    return res.json({ success: false, message: `❌ Ghế ${ghe_so} đã có người đặt!` });
                }
                
                this.db.run(`INSERT INTO ve (ten_nguoi_dat, chuyen_bay_id, ghe_so, danh_xung, sdt, email, cccd) 
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
        } else {
            // For MySQL
            this.db.query('SELECT * FROM ve WHERE chuyen_bay_id = ? AND ghe_so = ?', 
                [chuyen_bay_id, ghe_so], (err, results) => {
                if (err) {
                    return res.json({ success: false, message: 'Database error' });
                }
                
                if (results.length > 0) {
                    return res.json({ success: false, message: `❌ Ghế ${ghe_so} đã có người đặt!` });
                }
                
                this.db.query(`INSERT INTO ve (ten_nguoi_dat, chuyen_bay_id, ghe_so, danh_xung, sdt, email, cccd) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)`, 
                    [ten_nguoi_dat, chuyen_bay_id, ghe_so, danh_xung, sdt, email, cccd], (err, result) => {
                    if (err) {
                        return res.json({ success: false, message: 'Error booking ticket' });
                    }
                    
                    res.json({ 
                        success: true, 
                        message: `✅ Đặt vé thành công! Ghế: ${ghe_so}`,
                        ticketId: result.insertId
                    });
                });
            });
        }
    }

    // Show booking history
    showBookingHistory(req, res) {
        const query = `
            SELECT v.*, cb.ma_cb, cb.diem_di, cb.diem_den, cb.ngay_di, cb.gio_di, cb.gio_den, cb.hang_hang_khong, cb.gia
            FROM ve v
            JOIN chuyen_bay cb ON v.chuyen_bay_id = cb.id
            ORDER BY v.created_at DESC
        `;
        
        // For SQLite
        if (this.db.all) {
            this.db.all(query, (err, tickets) => {
                if (err) {
                    tickets = [];
                }
                
                res.render('lichsuve', { tickets, user: req.session.user });
            });
        } else {
            // For MySQL
            this.db.query(query, (err, tickets) => {
                if (err) {
                    tickets = [];
                }
                
                res.render('lichsuve', { tickets, user: req.session.user });
            });
        }
    }
}

module.exports = FlightController;