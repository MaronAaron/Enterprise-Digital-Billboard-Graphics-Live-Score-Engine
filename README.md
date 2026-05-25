Enterprise Digital Billboard Graphics & Live Score Engine
An enterprise-grade, real-time Digital Out-Of-Home (DOOH) broadcasting system built to seamlessly inject dynamic live sports events and automated match breakthroughs directly into active advertising displays.
Featuring an asymmetrical layout-shifting interface, the system splits screen real estate dynamically: standard events shrink the ongoing advertisement into a specialized side panel overlay, while critical high-stakes matches or intense VAR disputes trigger a full-screen display takeover.
🚀 Key System Features
•	Dynamic Screen-Allocation Architecture: Uses CSS custom variables combined with high-performance cubic-bezier layout transitions to dynamically shrink background media components without interrupting video loops or hardware refresh cycles.
•	Smart Fallback Controller: Includes a dual-state backend engine. If no live football fixtures are actively running globally, the API instantly defaults to an automated, persistent mock scenario carousel so the display screen never goes blank.
•	Dynamic Team Color Branding: Automatically analyzes incoming metadata to instantly adapt structural accents, borders, and typography glow to match the prominent club colors or official league themes.
•	Low-Overhead Cache Layer: Keeps API usage light by tracking match data states locally in a structured JSON memory cache layer (livescores.json), preventing endpoint rate-limiting blocks.









🛠️ Architecture & Data Workflow
The system is optimized for continuous playback on remote hardware players, media boxes, and LED signage networks using a classic decoupled polling design:
[ Public Display Client (index.php) ]
               │
               │ (Asynchronous Fetch Loop - Every 2s)
               ▼
   [ API Middleware Engine (api.php) ]
               │
               ├───► [ Active Local Cache (livescores.json) ]
               │
               ▼ (State Verification)
  [ External Live Football Provider (apifootball.com) ]
📂 Project Repository Structure
Plaintext
├── api.php             # Core Logic Engine: JSON parser, cURL client & event generator
├── index.php           # Front-End System: CSS Grid layout framework & animation loop
├── livescores.json     # State Storage: Local JSON file tracking historical goals
└── demo_step.txt       # State Counter: Tracks persistence loop timing when in Demo Mode
🔧 Installation & Deployment Guide
1. Environment Requirements
•	Web Server: Apache2, Nginx, or an integrated system like XAMPP / Laragon.
•	PHP Environment: PHP 7.4 or higher with the php-curl and json extensions enabled.
•	Network Access: Stable internet access on the hosting node to communicate with the external streaming data center.

2. Configuration Setup
Cloning and setting up the system takes less than two minutes:
1.	Clone this repository directly to your web root folder:
Bash
git clone https://github.com/yourusername/billboard-graphics-engine.git
2.	Open api.php and supply your personal authorization token line:
PHP
$apiKey = "YOUR_API_FOOTBALL_COMM_KEY_HERE";
3.	Set the operation flag based on your environment needs:
PHP
$DEMO_MODE = true;  // Set to TRUE to cycle the display simulation loop
$DEMO_MODE = false; // Set to FALSE to listen to active global live scores
4.	Assign appropriate storage permissions to your directory so the engine can safely read and modify local cache counters:












Bash
chmod 775 livescores.json demo_step.txt
📊 API Interface Specifications
When polled by a digital sign or display, the middleware engine yields a structured, uniform data payload to prevent parsing crashes on the terminal interface:
Sample Layout Return Object (Goal Event)
JSON
{
  "type": "goal",
  "home": "Vipers SC",
  "away": "KCCA FC",
  "h_score": 1,
  "a_score": 0,
  "home_logo": "https://apiv3.apifootball.com/badges/logo_match/14175_vipers.png",
  "away_logo": "https://apiv3.apifootball.com/badges/logo_match/14172_kcca.png",
  "league": "Uganda Premier League",
  "scorer": "Milton Karisa",
  "minute": "12'",
  "is_high_stakes": false,
  "team_color": "#E32221"
}





💎 Production Enhancements (Ready for Scale)
•	Asynchronous Collision Buffering: The client browser script uses an explicit state flag (isAnimating) to reject data parsing changes mid-transition, ensuring layout animations never clip or overlap during hyperactive match-days.
•	Network Timeout Isolation: The cURL transmission interface has a strict execution window limit of 5 seconds. If connection delays hit the node, it drops gracefully rather than freezing your public display script queue.
•	Hardware-Optimized Typography: Uses clean, compressed sans-serif font families (Oswald & Montserrat) paired with vw/vh sizing units to guarantee maximum legibility from long distances on high-density commercial LED screens.
📝 License
This project is open-source and available under the MIT License. Feel free to extend or modify it for commercial signage applications.

