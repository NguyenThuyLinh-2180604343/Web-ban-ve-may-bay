class AdminController {
    constructor(db) {
        this.db = db;
    }

    // Show admin dashboard
    showDashboard(req, res) {
        const statsQuery = `
            SELECT 
                (SELECT COUNT(*) FROM chuyen_bay) as total_flights,
                (SELECT COUNT(*) FROM ve) as total_tickets,
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT SUM(cb.gia) FROM ve v JOIN chuyen_bay cb ON v.chuyen_bay_id = cb.id WHERE v.trang_thai_thanh_toan = 1) as total_revenue
        `;
        
        // For SQLite
        if (this.db.get) {
            this.db.get(statsQuery, (err, stats) => {
                if (err) {
                    stats = { total_flights: 0, total_tickets: 0, total_users: 0, total_revenue: 0 };
                }
                
                res.render('admin/admin', { user: req.session.user, stats });
            });
        } else {
            // For MySQL
            this.db.query(statsQuery, (err, stats) => {
                if (err) {
                    stats = [{ total_flights: 0, total_tickets: 0, total_users: 0, total_revenue: 0 }];
                }
                
                res.render('admin/admin', { user: req.session.user, stats: stats[0] });
            });
        }
    }

    // Show flight management
    showFlightManagement(req, res) {
        // For SQLite
        if (this.db.all) {
            this.db.all('SELECT * FROM chuyen_bay ORDER BY ngay_di DESC', (err, flights) => {
                if (err) {
                    flights = [];
                }
                
                res.render('admin/sanphamadmin', { flights, user: req.session.user });
            });
        } else {
            // For MySQL
            this.db.query('SELECT * FROM chuyen_bay ORDER BY ngay_di DESC', (err, flights) => {
                if (err) {
                    flights = [];
                }
                
                res.render('admin/sanphamadmin', { flights, user: req.session.user });
            });
        }
    }

    // Add new flight
    addFlight(req, res) {
        const { ma_cb, diem_di, diem_den, ngay_di, gio_di, gio_den, hang_hang_khong, loai_may_bay, gia, tong_ghe } = req.body;
        
        const query = `INSERT INTO chuyen_bay (ma_cb, diem_di, diem_den, ngay_di, gio_di, gio_den, hang_hang_khong, loai_may_bay, gia, tong_ghe) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`;
        
        // For SQLite
        if (this.db.run) {
            this.db.run(query, [ma_cb, diem_di, diem_den, ngay_di, gio_di, gio_den, hang_hang_khong, loai_may_bay, gia, tong_ghe || 60], 
                function(err) {
                if (err) {
                    return res.json({ success: false, message: 'Error adding flight' });
                }
                
                res.json({ success: true, message: 'Flight added successfully' });
            });
        } else {
            // For MySQL
            this.db.query(query, [ma_cb, diem_di, diem_den, ngay_di, gio_di, gio_den, hang_hang_khong, loai_may_bay, gia, tong_ghe || 60], 
                (err, result) => {
                if (err) {
                    return res.json({ success: false, message: 'Error adding flight' });
                }
                
                res.json({ success: true, message: 'Flight added successfully' });
            });
        }
    }

    // Delete flight
    deleteFlight(req, res) {
        const flightId = req.params.id;
        
        // For SQLite
        if (this.db.run) {
            this.db.run('DELETE FROM ve WHERE chuyen_bay_id = ?', [flightId], (err) => {
                if (err) {
                    return res.json({ success: false, message: 'Error deleting flight' });
                }
                
                this.db.run('DELETE FROM chuyen_bay WHERE id = ?', [flightId], (err) => {
                    if (err) {
                        return res.json({ success: false, message: 'Error deleting flight' });
                    }
                    
                    res.json({ success: true, message: 'Flight deleted successfully' });
                });
            });
        } else {
            // For MySQL
            this.db.query('DELETE FROM ve WHERE chuyen_bay_id = ?', [flightId], (err) => {
                if (err) {
                    return res.json({ success: false, message: 'Error deleting flight' });
                }
                
                this.db.query('DELETE FROM chuyen_bay WHERE id = ?', [flightId], (err) => {
                    if (err) {
                        return res.json({ success: false, message: 'Error deleting flight' });
                    }
                    
                    res.json({ success: true, message: 'Flight deleted successfully' });
                });
            });
        }
    }
}

module.exports = AdminController;