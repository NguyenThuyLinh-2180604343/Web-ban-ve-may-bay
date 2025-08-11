const bcrypt = require('bcryptjs');

class AuthController {
    constructor(db) {
        this.db = db;
    }

    // Show login page
    showLogin(req, res) {
        res.render('login', { message: '' });
    }

    // Handle login
    async login(req, res) {
        const { username, password } = req.body;
        
        // For SQLite
        if (this.db.get) {
            this.db.get('SELECT * FROM users WHERE username = ?', [username], (err, user) => {
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
        } else {
            // For MySQL
            this.db.query('SELECT * FROM users WHERE username = ?', [username], (err, results) => {
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
        }
    }

    // Show register page
    showRegister(req, res) {
        res.render('register', { message: '' });
    }

    // Handle register
    async register(req, res) {
        const { username, password } = req.body;
        
        // Hash password
        const hashedPassword = await bcrypt.hash(password, 10);
        
        // For SQLite
        if (this.db.get) {
            this.db.get('SELECT * FROM users WHERE username = ?', [username], (err, user) => {
                if (err) {
                    return res.render('register', { message: 'Database error' });
                }
                
                if (user) {
                    return res.render('register', { message: '❌ Tên đăng nhập đã tồn tại.' });
                }
                
                this.db.run('INSERT INTO users (username, password, role) VALUES (?, ?, ?)', 
                    [username, hashedPassword, 'user'], (err) => {
                    if (err) {
                        return res.render('register', { message: 'Error creating account' });
                    }
                    
                    res.render('register', { message: '✅ Đăng ký thành công! Hãy đăng nhập.' });
                });
            });
        } else {
            // For MySQL
            this.db.query('SELECT * FROM users WHERE username = ?', [username], (err, results) => {
                if (err) {
                    return res.render('register', { message: 'Database error' });
                }
                
                if (results.length > 0) {
                    return res.render('register', { message: '❌ Tên đăng nhập đã tồn tại.' });
                }
                
                this.db.query('INSERT INTO users (username, password, role) VALUES (?, ?, ?)', 
                    [username, hashedPassword, 'user'], (err, result) => {
                    if (err) {
                        return res.render('register', { message: 'Error creating account' });
                    }
                    
                    res.render('register', { message: '✅ Đăng ký thành công! Hãy đăng nhập.' });
                });
            });
        }
    }

    // Handle logout
    logout(req, res) {
        req.session.destroy();
        res.redirect('/login');
    }
}

module.exports = AuthController;