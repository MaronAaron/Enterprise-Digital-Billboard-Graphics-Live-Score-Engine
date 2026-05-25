<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Billboard Graphics Engine</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700;900&family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --main-color: #ffcc00; 
            --panel-bg: linear-gradient(145deg, #16161a, #0b0b0d);
        }
        body, html { 
            margin: 0; padding: 0; width: 100vw; height: 100vh; 
            background: #000; overflow: hidden; 
            font-family: 'Oswald', sans-serif; display: flex; 
        }

        /* PREMIUM BACKGROUND ADS CONTAINER */
        #ad-container {
            width: 100%; height: 100%; 
            transition: width 0.85s cubic-bezier(0.87, 0, 0.13, 1);
            position: relative; z-index: 1;
            background: #111;
        }
        #ad-container img { width: 100%; height: 100%; object-fit: cover; }
        
        /* Modern UI Ambient Ad Tint Overlay Overlay */
        #ad-container::after {
            content: ''; position: absolute; top: 0; left: 0; 
            width: 100%; height: 100%; background: rgba(0, 0, 0, 0.15);
        }

        /* GRAPHICS PANEL OVERLAY SYSTEM */
        #graphics-overlay {
            position: fixed; display: flex; flex-direction: column; 
            align-items: center; justify-content: center;
            background: var(--panel-bg); color: white;
            transition: all 0.85s cubic-bezier(0.87, 0, 0.13, 1); z-index: 10;
            box-shadow: -10px 0 30px rgba(0,0,0,0.5);
        }

        /* STATE MANAGEMENT MATRICES */
        .state-hidden { right: -105%; top: 0; width: 32%; height: 100%; opacity: 0; }
        
        /* Corner/Side Popout Matrix */
        .state-side { right: 0; top: 0; width: 32%; height: 100%; opacity: 1; border-left: 8px solid var(--main-color); }
        .ad-shrink { width: 68% !important; } 

        /* High Stakes Full Screen Takeover Frame */
        .state-full { right: 0; top: 0; width: 100%; height: 100%; opacity: 1; background: radial-gradient(circle at center, #1a1a24 0%, #07070a 100%); }

        /* BRAND DISPLAY COMPONENTS */
        .header { 
            font-size: 3.8vw; font-weight: 900; letter-spacing: 6px; 
            text-transform: uppercase; color: var(--main-color); 
            margin-bottom: 3vh; text-align: center;
            text-shadow: 0 4px 10px rgba(0,0,0,0.4);
        }
        .var-header { color: #ff3333; animation: system-pulse 1.2s infinite ease-in-out; }
        
        @keyframes system-pulse { 
            0%, 100% { opacity: 1; transform: scale(1); } 
            50% { opacity: 0.6; transform: scale(0.97); } 
        }

        .scoreboard { display: flex; align-items: center; justify-content: center; width: 100%; }
        .state-side .scoreboard { flex-direction: column; gap: 4vh; }
        .state-full .scoreboard { flex-direction: row; gap: 6vw; }

        .team { display: flex; flex-direction: column; align-items: center; transition: all 0.3s; }
        .logo { object-fit: contain; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.85)); }
        
        .state-side .logo { width: 18vh; height: 18vh; }
        .state-full .logo { width: 28vh; height: 28vh; }
        
        .name { font-size: 2.2vw; margin-top: 1.5vh; text-transform: uppercase; text-align: center; font-weight: 700; letter-spacing: 1px; }

        .score-box { 
            background: rgba(255,255,255,0.04); border-radius: 16px; text-align: center; 
            border: 3px solid var(--main-color); padding: 2vh 3.5vw;
            box-shadow: inset 0 0 20px rgba(255,255,255,0.02), 0 10px 25px rgba(0,0,0,0.5);
        }
        .score { font-size: 7.5vw; font-weight: 900; line-height: 1; color: white; letter-spacing: -2px; }

        .scorer-info { 
            margin-top: 4vh; text-align: center; background: var(--main-color); 
            color: #000; padding: 1.2vh 3.5vw; border-radius: 50px; 
            font-size: 1.8vw; font-weight: 900; text-transform: uppercase;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            font-family: 'Montserrat', sans-serif;
        }
        
        .league-footer { 
            position: absolute; bottom: 4vh; font-size: 1.3vw; 
            color: rgba(255,255,255,0.4); letter-spacing: 4px; 
            text-transform: uppercase; font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div id="ad-container">
        <img src="https://images.unsplash.com/photo-1773332611612-ffdaa753afb1?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Billboard Ad Frame">
    </div>

    <div id="graphics-overlay" class="state-hidden">
        <div id="alert-header" class="header">GOAL!</div>
        
        <div class="scoreboard">
            <div class="team">
                <img id="h-logo" class="logo" src="" alt="Home Badge">
                <div id="h-name" class="name">HOME</div>
            </div>

            <div class="score-box">
                <div id="score-text" class="score">0 - 0</div>
            </div>

            <div class="team">
                <img id="a-logo" class="logo" src="" alt="Away Badge">
                <div id="a-name" class="name">AWAY</div>
            </div>
        </div>

        <div id="scorer-text" class="scorer-info">PLAYER NAME 90'</div>
        <div id="league-name" class="league-footer">LEAGUE</div>
    </div>

    <script>
        const overlay = document.getElementById('graphics-overlay');
        const adContainer = document.getElementById('ad-container');
        let isAnimating = false;

        function checkScore() {
            if (isAnimating) return; 

            fetch('api.php')
            .then(response => {
                if (!response.ok) throw new Error('Network response code failure');
                return response.json();
            })
            .then(data => {
                if (data && data.type) {
                    isAnimating = true;

                    // 1. Core Injection Matrix
                    document.documentElement.style.setProperty('--main-color', data.team_color || '#ffcc00');
                    document.getElementById('h-name').innerText = data.home;
                    document.getElementById('a-name').innerText = data.away;
                    document.getElementById('h-logo').src = data.home_logo || 'https://apiv3.apifootball.com/badges/logo_match/fallback.png';
                    document.getElementById('a-logo').src = data.away_logo || 'https://apiv3.apifootball.com/badges/logo_match/fallback.png';
                    document.getElementById('score-text').innerText = data.h_score + " - " + data.a_score;
                    document.getElementById('scorer-text').innerText = data.scorer + " " + data.minute;
                    document.getElementById('league-name').innerText = data.league;

                    // 2. Type Configuration Formatting Engine
                    const header = document.getElementById('alert-header');
                    if (data.type === 'var') {
                        header.innerText = "VAR DECISION";
                        header.classList.add('var-header');
                    } else {
                        header.innerText = "GOAL!";
                        header.classList.remove('var-header');
                    }

                    // 3. Layout Positioning Operations
                    let displayDuration = 15000; // 15 Seconds (Optimized standard for public displays)
                    overlay.className = ''; // Hard reset style configuration state safely
                    
                    if (data.is_high_stakes || data.type === 'var') {
                        overlay.classList.add('state-full');
                        displayDuration = 20000; // 20 Seconds window for macro events
                    } else {
                        overlay.classList.add('state-side');
                        adContainer.classList.add('ad-shrink');
                    }

                    // 4. Safe Teardown Chain Execution
                    setTimeout(() => {
                        overlay.className = 'state-hidden';
                        adContainer.classList.remove('ad-shrink');
                        
                        // Buffer transition cool-down to completely clear asynchronous overlaps
                        setTimeout(() => { isAnimating = false; }, 1200);
                    }, displayDuration);
                }
            })
            .catch(err => console.warn("Polling system active: Waiting for live score events..."));
        }

        // Initialize execution grid safely at 2-second intervals
        setInterval(checkScore, 2000);
        checkScore(); // Instant execution pool on frame init
    </script>
</body>
</html>
