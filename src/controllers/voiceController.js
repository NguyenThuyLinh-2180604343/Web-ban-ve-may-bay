class VoiceController {
    constructor(db) {
        this.db = db;
    }

    // Process voice search request
    async processVoiceSearch(req, res) {
        try {
            const { transcript, sessionId } = req.body;
            
            if (!transcript) {
                return res.json({
                    success: false,
                    message: 'No transcript provided'
                });
            }

            // Process the voice input
            const result = this.parseFlightSearch(transcript);
            
            // If it's a flight search, get flight data
            if (result.isFlightSearch && result.from && result.to) {
                const flights = await this.searchFlights(result.from, result.to, result.date);
                
                return res.json({
                    success: true,
                    type: 'flight_search',
                    data: {
                        searchParams: result,
                        flights: flights,
                        message: `Tìm thấy ${flights.length} chuyến bay từ ${result.from} đến ${result.to}${result.date ? ` ngày ${result.date}` : ''}`
                    }
                });
            }
            
            // General conversation
            const response = this.generateResponse(transcript);
            
            res.json({
                success: true,
                type: 'conversation',
                data: {
                    message: response,
                    suggestions: this.getSuggestions()
                }
            });

        } catch (error) {
            console.error('Voice processing error:', error);
            res.status(500).json({
                success: false,
                message: 'Lỗi xử lý giọng nói'
            });
        }
    }

    // Parse flight search from voice input
    parseFlightSearch(input) {
        const lowerInput = input.toLowerCase();
        
        // Location mapping
        const locations = {
            'hà nội': 'Hà Nội',
            'hanoi': 'Hà Nội',
            'sài gòn': 'TP.HCM',
            'saigon': 'TP.HCM',
            'hcm': 'TP.HCM',
            'tp hcm': 'TP.HCM',
            'hồ chí minh': 'TP.HCM',
            'đà nẵng': 'Đà Nẵng',
            'da nang': 'Đà Nẵng',
            'nha trang': 'Nha Trang',
            'phú quốc': 'Phú Quốc',
            'phu quoc': 'Phú Quốc',
            'cần thơ': 'Cần Thơ',
            'can tho': 'Cần Thơ'
        };

        let from = null, to = null, date = null;
        let isFlightSearch = false;

        // Check if it's a flight search
        const searchKeywords = ['tìm vé', 'vé từ', 'bay từ', 'đi từ', 'chuyến bay', 'giá vé', 'book vé'];
        isFlightSearch = searchKeywords.some(keyword => lowerInput.includes(keyword));

        if (isFlightSearch) {
            // Extract from/to pattern: "từ X đi Y" or "từ X đến Y"
            const fromToPattern = /từ\s+([^đ]+)\s+(?:đ[iế]n?|đi)\s+(.+?)(?:\s+ngày|$)/i;
            const match = lowerInput.match(fromToPattern);
            
            if (match) {
                const fromText = match[1].trim();
                const toText = match[2].trim();
                
                from = this.findLocation(fromText, locations);
                to = this.findLocation(toText, locations);
            }

            // Extract date if mentioned
            const datePattern = /ngày\s+(\d{1,2})[\/\-](\d{1,2})[\/\-]?(\d{2,4})?/i;
            const dateMatch = lowerInput.match(datePattern);
            if (dateMatch) {
                const day = dateMatch[1].padStart(2, '0');
                const month = dateMatch[2].padStart(2, '0');
                const year = dateMatch[3] ? 
                    (dateMatch[3].length === 2 ? '20' + dateMatch[3] : dateMatch[3]) : 
                    new Date().getFullYear();
                date = `${year}-${month}-${day}`;
            }
        }

        return { isFlightSearch, from, to, date };
    }

    // Find closest location match
    findLocation(input, locations) {
        const cleanInput = input.toLowerCase().trim();
        
        // Exact match first
        if (locations[cleanInput]) {
            return locations[cleanInput];
        }
        
        // Partial match
        for (const [key, value] of Object.entries(locations)) {
            if (key.includes(cleanInput) || cleanInput.includes(key)) {
                return value;
            }
        }
        
        return cleanInput; // Return as-is if no match
    }

    // Search flights in database
    async searchFlights(from, to, date) {
        return new Promise((resolve, reject) => {
            let query = `SELECT * FROM chuyen_bay WHERE diem_di LIKE ? AND diem_den LIKE ?`;
            let params = [`%${from}%`, `%${to}%`];
            
            if (date) {
                query += ` AND ngay_di = ?`;
                params.push(date);
            }
            
            query += ` ORDER BY ngay_di, gio_di LIMIT 10`;

            // For SQLite
            if (this.db.all) {
                this.db.all(query, params, (err, flights) => {
                    if (err) {
                        reject(err);
                    } else {
                        resolve(flights || []);
                    }
                });
            } else {
                // For MySQL
                this.db.query(query, params, (err, flights) => {
                    if (err) {
                        reject(err);
                    } else {
                        resolve(flights || []);
                    }
                });
            }
        });
    }

    // Generate conversational response
    generateResponse(input) {
        const lowerInput = input.toLowerCase();
        
        const responses = {
            'chào': 'Xin chào! Tôi có thể giúp bạn tìm kiếm chuyến bay bằng giọng nói. Hãy nói: "Tìm vé từ Hà Nội đi Sài Gòn"',
            'hello': 'Hello! I can help you search for flights using voice. Try saying: "Find flights from Hanoi to Ho Chi Minh City"',
            'help': 'Tôi có thể giúp bạn: 🎤 Tìm chuyến bay bằng giọng nói, ✈️ Kiểm tra giá vé, 📅 Xem lịch bay. Thử nói: "Tìm vé từ [điểm đi] đi [điểm đến]"',
            'giúp': 'Tôi có thể giúp bạn tìm kiếm chuyến bay. Hãy nói điểm đi và điểm đến, ví dụ: "Tìm vé từ Hà Nội đi Đà Nẵng"',
            'cảm ơn': 'Không có gì! Tôi luôn sẵn sàng giúp bạn tìm kiếm chuyến bay.',
            'thank': 'You\'re welcome! I\'m always here to help you find flights.'
        };

        // Check for keyword matches
        for (const [keyword, response] of Object.entries(responses)) {
            if (lowerInput.includes(keyword)) {
                return response;
            }
        }

        // Default response
        return 'Tôi chưa hiểu rõ yêu cầu của bạn. Bạn có thể nói: "Tìm vé từ Hà Nội đi Sài Gòn" hoặc "Giá vé đi Đà Nẵng"';
    }

    // Get conversation suggestions
    getSuggestions() {
        return [
            "Tìm vé từ Hà Nội đi Sài Gòn",
            "Giá vé đi Đà Nẵng",
            "Chuyến bay từ TP.HCM đi Phú Quốc",
            "Vé máy bay giá rẻ"
        ];
    }

    // Text-to-speech endpoint
    async textToSpeech(req, res) {
        try {
            const { text, voice = 'vi-VN', rate = 1.0 } = req.body;
            
            // For now, return the text for client-side TTS
            // In production, integrate with AWS Polly or Google TTS
            res.json({
                success: true,
                text: text,
                audioUrl: null, // Will be generated client-side
                voice: voice,
                rate: rate
            });
            
        } catch (error) {
            console.error('TTS error:', error);
            res.status(500).json({
                success: false,
                message: 'Lỗi tạo giọng nói'
            });
        }
    }
}

module.exports = VoiceController;