const express = require('express');
const router = express.Router();

const AuthController = require('../controllers/authController');
const FlightController = require('../controllers/flightController');
const AdminController = require('../controllers/adminController');
const VoiceController = require('../controllers/voiceController');
const { requireAuth, requireAdmin } = require('../middleware/auth');

// Initialize controllers
let authController, flightController, adminController, voiceController;

const initializeRoutes = (db) => {
    authController = new AuthController(db);
    flightController = new FlightController(db);
    adminController = new AdminController(db);
    voiceController = new VoiceController(db);
    
    return router;
};

// Public routes
router.get('/login', (req, res) => authController.showLogin(req, res));
router.post('/login', (req, res) => authController.login(req, res));
router.get('/register', (req, res) => authController.showRegister(req, res));
router.post('/register', (req, res) => authController.register(req, res));
router.get('/logout', (req, res) => authController.logout(req, res));

// Protected routes
router.get('/', requireAuth, (req, res) => flightController.showHome(req, res));
router.get('/showticket', requireAuth, (req, res) => flightController.searchFlights(req, res));
router.get('/chitiet/:id', requireAuth, (req, res) => flightController.showFlightDetails(req, res));
router.post('/dat-ve', requireAuth, (req, res) => flightController.bookTicket(req, res));
router.get('/lichsuve', requireAuth, (req, res) => flightController.showBookingHistory(req, res));

// Admin routes
router.get('/admin', requireAdmin, (req, res) => adminController.showDashboard(req, res));
router.get('/admin/sanphamadmin', requireAdmin, (req, res) => adminController.showFlightManagement(req, res));
router.post('/admin/add-flight', requireAdmin, (req, res) => adminController.addFlight(req, res));
router.delete('/admin/delete-flight/:id', requireAdmin, (req, res) => adminController.deleteFlight(req, res));

// Voice API routes
router.post('/api/voice/search', requireAuth, (req, res) => voiceController.processVoiceSearch(req, res));
router.post('/api/voice/tts', requireAuth, (req, res) => voiceController.textToSpeech(req, res));

// Demo page
router.get('/voice-demo', requireAuth, (req, res) => {
    res.render('voice-demo', { user: req.session.user });
});

module.exports = { router, initializeRoutes };