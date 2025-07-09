<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>complete responsive tour and travel agency website design tutorial</title>

    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="style.css">

</head>

<body>

    <!-- header section starts  -->

    <header>

        <div id="menu-bar" class="fas fa-bars"></div>

        <a href="index.php" class="logo" style="color:#fff;">
            <span style="color:#64b5f6;">T</span>rave<span style="color:#64b5f6;">l</span>
        </a>

        <nav class="navbar">
            <a href="#home" class="nav-link">home</a>
            <a href="lichsuve.php" class="nav-link">history</a>
        </nav>
        <style>
            .navbar .nav-link:active,
            .navbar .nav-link:focus,
            .navbar .nav-link.active {
                background: #bbdefb !important;
                color: #1976d2 !important;
                border-radius: 6px;
                transition: background 0.2s;
            }

            .navbar .nav-link:hover {
                background:rgb(55, 61, 65) !important;
                color: #1976d2 !important;
                border-radius: 6px;
                transition: background 0.2s;
            }

            .icons #search-btn {
                color: #fff;
                background: transparent;
                border-radius: 6px;
                padding: 6px 10px;
                transition: background 0.2s, color 0.2s;
            }

            .icons #search-btn:hover,
            .icons #search-btn:focus,
            .icons #search-btn.active {
                background:rgb(67, 73, 78) !important;
                color: #1976d2 !important;
            }
        </style>

        <div class="icons">
            <i class="fas fa-search" id="search-btn" style="cursor:pointer;"></i>
            <!-- Form tìm kiếm ẩn, hiện khi nhấp vào icon tìm kiếm -->
            <div class="search-bar-container" style="display:none; position:absolute; right:0; top:40px; background:#fff; padding:10px; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.1); z-index:100;">
                <form method="GET" action="tim-kiem.php" style="display:flex; gap:8px;">
                    <input type="text" id="search-bar" name="tu_khoa" placeholder="Nhập thông tin..." style="padding:6px 10px; border:1px solid #ccc; border-radius:4px;">
                    <button type="submit" style="background:#0077cc; color:#fff; border:none; border-radius:4px; padding:6px 12px; cursor:pointer;">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                </form>
            </div>
            <div style="position: relative; display: inline-block;">
                <?php if (isset($_SESSION['username'])): ?>
                    <span style="font-weight:bold; color:#0077cc; font-size: 1.25em;">
                        <?= htmlspecialchars($_SESSION['username']) ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="admin/sanphamadmin.php" style="color:red; font-weight:bold; margin-left:10px;">[Admin]</a>
                        <?php endif; ?>
                    </span>
                    <a href="logout.php" style="margin-left:15px; color:#e53935; font-weight:bold; border:1px solidrgb(164, 191, 237); border-radius:5px; padding:6px 16px; background:#fff; text-decoration:none;">Logout</a>
                <?php else: ?>
                    <a href="login.php" style="margin-right:12px; color:#0077cc; font-weight:bold; border:1px solid #0077cc; border-radius:5px; padding:6px 16px; background:#fff; text-decoration:none;">Login</a>
                    <a href="register.php" style="color:#fff; background:#0077cc; font-weight:bold; border-radius:5px; padding:6px 16px; text-decoration:none; border:1px solid #0077cc;">Register</a>
                <?php endif; ?>
            </div>

        </div>

    </header>

    <!-- header section ends -->

    <!-- login form container  -->

    <div class="login-form-container">

        <i class="fas fa-times" id="form-close"></i>

        <form action="">
            <h3>login</h3>
            <input type="email" class="box" placeholder="enter your email">
            <input type="password" class="box" placeholder="enter your password">
            <input type="submit" value="login now" class="btn">
            <input type="checkbox" id="remember">
            <label for="remember">remember me</label>
            <p>forget password? <a href="#">click here</a></p>
            <p>don't have and account? <a href="#">register now</a></p>
        </form>

    </div>
    <script>
        document.getElementById('login-btn').addEventListener('click', function() {
            const menu = document.getElementById('user-menu');
            if (menu) {
                menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
            }
        });

        // Ẩn khi click ngoài
        document.addEventListener('click', function(event) {
            const loginBtn = document.getElementById('login-btn');
            const menu = document.getElementById('user-menu');
            if (menu && !loginBtn.contains(event.target) && !menu.contains(event.target)) {
                menu.style.display = 'none';
            }
        });

        // Hiện/ẩn form tìm kiếm khi nhấp vào icon tìm kiếm
        document.getElementById('search-btn').addEventListener('click', function(event) {
            event.stopPropagation();
            const searchForm = document.querySelector('.search-bar-container');
            if (searchForm) {
                searchForm.style.display = (searchForm.style.display === 'none' || searchForm.style.display === '') ? 'block' : 'none';
                if (searchForm.style.display === 'block') {
                    document.getElementById('search-bar').focus();
                }
            }
        });

        // Ẩn form tìm kiếm khi click ra ngoài
        document.addEventListener('click', function(event) {
            const searchForm = document.querySelector('.search-bar-container');
            const searchBtn = document.getElementById('search-btn');
            if (searchForm && !searchForm.contains(event.target) && event.target !== searchBtn) {
                searchForm.style.display = 'none';
            }
        });
    </script>

    <!-- Chatbot fixed bottom right -->
    <div id="chatbot-container" style="position:fixed; bottom:24px; right:24px; z-index:9999; width:340px; max-width:90vw;">
        <div id="chatbot-header" style="background:#1976d2; color:#fff; padding:12px 16px; border-radius:12px 12px 0 0; font-weight:bold; cursor:pointer;">
            Support Chatbot <span style="float:right;"><i class="fas fa-comment-dots"></i></span>
        </div>
        <div id="chatbot-body" style="background:#fff; border:1px solid #1976d2; border-top:none; border-radius:0 0 12px 12px; max-height:340px; overflow-y:auto; padding:12px; display:none;">
            <div id="chatbot-messages" style="font-size:1em; min-height:80px; margin-bottom:10px;"></div>
            <form id="chatbot-form" style="display:flex; gap:8px;">
                <input type="text" id="chatbot-input" placeholder="Type your question..." style="flex:1; padding:8px 10px; border:1px solid #bbb; border-radius:6px;">
                <button type="submit" style="background:#1976d2; color:#fff; border:none; border-radius:6px; padding:8px 16px; font-weight:bold; cursor:pointer;">Send</button>
            </form>
        </div>
    </div>
    <script>
        // Toggle chatbot
        document.getElementById('chatbot-header').onclick = function() {
            const body = document.getElementById('chatbot-body');
            body.style.display = (body.style.display === 'none' || body.style.display === '') ? 'block' : 'none';
        };

        // Simple chatbot logic (demo)
        document.getElementById('chatbot-form').onsubmit = function(e) {
            e.preventDefault();
            const input = document.getElementById('chatbot-input');
            const msg = input.value.trim();
            if (!msg) return;
            const messages = document.getElementById('chatbot-messages');
            messages.innerHTML += '<div style="margin-bottom:6px;"><b>You:</b> ' + msg + '</div>';
            // Simple bot reply
            let reply = "Sorry, I don't understand your question yet.";
            if (/hello|hi|chào/i.test(msg)) reply = "Hello! How can I help you?";
            if (/vé|giá|bay|flight|ticket/i.test(msg)) reply = "You want to ask about flight tickets, please provide more details.";
            setTimeout(() => {
                messages.innerHTML += '<div style="margin-bottom:10px; color:#1976d2;"><b>Bot:</b> ' + reply + '</div>';
                messages.scrollTop = messages.scrollHeight;
            }, 500);
            input.value = '';
        };
    </script>
</body>

</html>