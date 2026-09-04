<?php
/**
 * Dynamic Careers & Opportunities Portal (Server-Side Active Filtering)
 * Centre for Integrated Health Programs (CIHP)
 */

$jobsFile = __DIR__ . '/data/jobs.json';
$allJobsRaw = file_exists($jobsFile) ? json_decode(file_get_contents($jobsFile), true) : [];
if (!is_array($allJobsRaw)) { $allJobsRaw = []; }

// Filter server-side: ONLY Active jobs are rendered
$activeJobsServer = array_values(array_filter($allJobsRaw, function($j) {
    return ($j['status'] ?? 'Active') === 'Active';
}));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Careers & Opportunities — Centre for Integrated Health Programs (CIHP)</title>
  <meta name="description" content="Explore public health careers, fellowships, consultancies (STTA), and vendor EOI solicitations at Centre for Integrated Health Programs (CIHP). Join 11M+ lives transformed." />

  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-blue: #1A54A0;
      --primary-blue-dark: #0B2F3A;
      --primary-blue-light: #256bbd;
      --secondary-green: #2F924A;
      --secondary-green-dark: #216934;
      --secondary-green-light: #EBF7EE;
      --accent-ochre: #8BED01;

      --bg-deep: #F4F1EA;
      --bg-surface: #ffffff;
      --bg-muted: #F4F1EA;
      --border-color: rgba(26, 84, 160, 0.12);

      --text-main: #0B2F3A;
      --text-muted: #385966;
      --text-light: #527583;

      --shadow-sm: 0 2px 8px rgba(15, 23, 42, 0.04);
      --shadow-md: 0 10px 25px rgba(30, 51, 136, 0.08);
      --shadow-lg: 0 20px 40px rgba(30, 51, 136, 0.12);

      --radius-sm: 6px;
      --radius-md: 12px;
      --radius-lg: 18px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: 'Inter', -apple-system, sans-serif;
      background-color: var(--bg-deep);
      color: var(--text-main);
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
    }
    .mono { font-family: 'JetBrains Mono', monospace; }

    .container { max-width: 1240px; margin: 0 auto; padding: 0 24px; }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-light));
      color: #ffffff; padding: 12px 24px; border-radius: var(--radius-sm); font-size: 0.92rem; font-weight: 700; text-decoration: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-emerald {
      background: var(--secondary-green); color: #ffffff; padding: 12px 24px; border-radius: var(--radius-sm); font-size: 0.92rem; font-weight: 700; text-decoration: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-outline {
      background: transparent; border: 2px solid var(--primary-blue); color: var(--primary-blue); padding: 10px 20px; border-radius: var(--radius-sm); font-weight: 700; font-size: 0.88rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
    }

    .navbar {
      position: sticky; top: 0; left: 0; width: 100%; background: #ffffff; backdrop-filter: blur(16px); border-bottom: 3px solid #1e3388; display: flex; justify-content: space-between; align-items: center; padding: 14px 44px; z-index: 10000; box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
    }
    .brand-logo { display: flex; align-items: center; gap: 16px; text-decoration: none; }
    .brand-logo img { height: 48px; filter: drop-shadow(0 2px 4px rgba(30, 51, 136, 0.12)); }
    .presence-badge { display: flex; align-items: center; gap: 8px; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 5px 14px; border-radius: 4px; font-size: 0.76rem; font-weight: 700; color: #1e3388; }
    .nav-links { display: flex; gap: 22px; list-style: none; align-items: center; }
    .nav-links a { color: #0f172a; text-decoration: none; font-size: 0.88rem; font-weight: 600; padding: 6px 0; }
    .nav-links a.active { color: #1e3388; font-weight: 700; }

    .careers-hero { background: linear-gradient(135deg, #090f2a 0%, #1e3388 60%, #1b5430 100%); color: #ffffff; padding: 70px 0 60px; }
    .careers-hero h1 { font-size: clamp(2rem, 3.8vw, 3rem); font-weight: 900; line-height: 1.15; margin-bottom: 16px; }

    .filter-bar-card {
      background: #ffffff; border-radius: var(--radius-md); padding: 24px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.18); display: grid; grid-template-columns: 2fr 1.2fr 1.2fr 1.2fr 0.8fr; gap: 12px; align-items: center;
    }
    .filter-input { width: 100%; padding: 12px 14px; border: 1.5px solid #cbd5e1; border-radius: var(--radius-sm); font-size: 0.88rem; color: var(--text-main); }

    section { padding: 70px 0; border-bottom: 1px solid var(--border-color); }
    .section-eyebrow { display: inline-flex; align-items: center; gap: 8px; color: var(--secondary-green); font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 12px; background: var(--secondary-green-light); padding: 4px 12px; border-radius: 50px; }
    .section-title { font-size: clamp(1.8rem, 3vw, 2.4rem); font-weight: 900; color: var(--primary-blue-dark); margin-bottom: 12px; }

    .jobs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 24px; }
    .job-card { background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 28px; transition: all 0.3s ease; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm); }
    .job-card:hover { border-color: var(--primary-blue); box-shadow: var(--shadow-md); transform: translateY(-3px); }
    .job-dept-badge { display: inline-block; background: var(--secondary-green-light); color: var(--secondary-green-dark); font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 4px; text-transform: uppercase; }
    .contract-type-pill { display: inline-block; background: #e0f2fe; color: #0369a1; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 50px; margin-left: 6px; }

    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); z-index: 99999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; padding: 20px; }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-card { background: #ffffff; border-radius: var(--radius-lg); max-width: 740px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 36px; position: relative; }
    .modal-close-btn { position: absolute; top: 20px; right: 20px; background: #f1f5f9; border: none; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; }
    .file-upload-box { border: 2px dashed #cbd5e1; border-radius: var(--radius-md); padding: 20px; text-align: center; background: var(--bg-deep); position: relative; cursor: pointer; }
    .file-upload-box input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
  </style>
</head>

<body>

  <nav class="navbar">
    <a href="index.html" class="brand-logo">
      <img src="img/cihp logo vector transparent coloured (1).png" alt="CIHP Logo" />
      <div class="presence-badge mono"><i class="fa-solid fa-earth-africa" style="color: var(--secondary-green);"></i> 20 States | 5 Pan-African Footholds</div>
    </a>

    <ul class="nav-links">
      <li><a href="index.html">Home</a></li>
      <li><a href="about.html">About CIHP</a></li>
      <li><a href="index.html#what-we-do">What We Do</a></li>
      <li><a href="news.html">News & Media</a></li>
      <li><a href="careers.html" class="active">Careers & EOI</a></li>
      <li><a href="hr_login.html" style="color: var(--accent-ochre); font-weight: 700;"><i class="fa-solid fa-lock"></i> HR Admin</a></li>
      <li><a href="contact.html" class="btn-primary" style="padding: 8px 18px;">Partner With Us</a></li>
    </ul>
  </nav>

  <header class="careers-hero">
    <div class="container">
      <div class="careers-hero-content">
        <div class="mono" style="background: rgba(255,255,255,0.12); padding: 6px 16px; border-radius: 50px; display: inline-block; font-size: 0.8rem; font-weight: 700; margin-bottom: 16px;"><i class="fa-solid fa-user-plus"></i> CIHP TALENT & OPPORTUNITIES NETWORK</div>
        <h1>Build Your Career in Sustainable Health Systems</h1>
        <p>Join a multidisciplinary team of public health leaders, clinical mentors, and data scientists transforming over 11 Million lives across Africa.</p>

        <div class="filter-bar-card">
          <input type="text" id="searchInput" class="filter-input" placeholder="Search title, skills, keyword..." onkeyup="filterJobs()" />
          
          <select id="deptFilter" class="filter-input" onchange="filterJobs()">
            <option value="">All Departments</option>
            <option value="Clinical Care & ART">Clinical Care & ART</option>
            <option value="M&E / Strategic Information">M&E / Strategic Info</option>
            <option value="Health Systems Strengthening">Health Systems (HSS)</option>
            <option value="Finance & Grants">Finance & Grants</option>
            <option value="Supply Chain & Logistics">Supply Chain & Logistics</option>
          </select>

          <select id="locationFilter" class="filter-input" onchange="filterJobs()">
            <option value="">All Locations</option>
            <option value="Abuja">Abuja HQ</option>
            <option value="Lagos">Lagos State</option>
            <option value="Gombe">Gombe State</option>
            <option value="Niger">Niger State</option>
            <option value="Benue">Benue State</option>
          </select>

          <select id="typeFilter" class="filter-input" onchange="filterJobs()">
            <option value="">All Contract Types</option>
            <option value="Full-Time">Full-Time</option>
            <option value="Fixed-Term">Fixed-Term (Grant-Funded)</option>
            <option value="Consultancy / STTA">Consultancy / STTA</option>
            <option value="Fellowship / Internship">Fellowship / Internship</option>
          </select>

          <button class="btn-primary" onclick="filterJobs()"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
        </div>
      </div>
    </div>
  </header>

  <main>
    <section>
      <div class="container">
        <div class="section-eyebrow"><i class="fa-solid fa-briefcase"></i> Active Vacancies</div>
        <h2 class="section-title">Open Positions (Dynamic Server-Filtered)</h2>

        <div class="jobs-grid" id="jobsGrid">
          <!-- Dynamic Server Rendered Active Jobs -->
        </div>

        <div style="background: linear-gradient(135deg, #0f172a, #1e3388); color: #ffffff; padding: 32px; border-radius: var(--radius-lg); margin-top: 40px; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">
          <div>
            <span class="mono" style="background: var(--accent-ochre); padding: 3px 10px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Procurement Solicitation</span>
            <h3 style="font-size: 1.25rem; font-weight: 800; margin: 8px 0 4px;">Expression of Interest (EOI) — Vendor Pre-Qualification</h3>
            <p style="font-size: 0.88rem; color: rgba(255, 255, 255, 0.85);">Group Life Insurance & Physical Assets Brokerage (2026/2027). Closing Sept 7, 2026.</p>
          </div>
          <a href="eoi.html" class="btn-emerald">Apply For EOI Online <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    <!-- Donor & Partner Moving Marquee Carousel -->
    <section class="partner-marquee-section">
      <div class="container">
        <div class="partner-marquee-header">
          <div class="partner-marquee-badge">
            <i class="fa-solid fa-handshake-angle"></i> Global Strategic Network
          </div>
          <p class="partner-marquee-title">
            Trusted by World-Class Donors, Multilateral Agencies & Strategic Partners
          </p>
        </div>

        <div class="partner-marquee-container">
          <div class="partner-marquee-track">
            <!-- Set 1 -->
            <div class="partner-logo-card" title="PEPFAR">
              <img src="img/partners/pepfar.jfif" alt="PEPFAR Logo" />
            </div>
            <div class="partner-logo-card" title="European Union">
              <img src="img/partners/European Union.png" alt="European Union Logo" />
            </div>
            <div class="partner-logo-card" title="Unitaid">
              <img src="img/partners/unitaid.png" alt="Unitaid Logo" />
            </div>
            <div class="partner-logo-card" title="Bill & Melinda Gates Foundation">
              <img src="img/partners/gate foundation.png" alt="Bill & Melinda Gates Foundation Logo" />
            </div>
            <div class="partner-logo-card" title="The Global Fund">
              <img src="img/partners/The-Global-Fund-Fighting-against-AIDS-tuberculosis-and-malaria.png"
                alt="The Global Fund Logo" />
            </div>
            <div class="partner-logo-card" title="GAVI Vaccine Alliance">
              <img src="img/partners/gavi.png" alt="GAVI Vaccine Alliance Logo" />
            </div>
            <div class="partner-logo-card" title="World Health Organization">
              <img src="img/partners/who.png" alt="World Health Organization Logo" />
            </div>
            <div class="partner-logo-card" title="Federal Ministry of Health Nigeria">
              <img src="img/partners/federal ministry of health.png"
                alt="Federal Ministry of Health Nigeria Logo" />
            </div>
            <div class="partner-logo-card" title="NACA Nigeria">
              <img src="img/partners/NACA.jpg" alt="NACA Logo" />
            </div>

            <!-- Set 2 (Duplicate for Seamless Loop) -->
            <div class="partner-logo-card" title="PEPFAR">
              <img src="img/partners/pepfar.jfif" alt="PEPFAR Logo" />
            </div>
            <div class="partner-logo-card" title="European Union">
              <img src="img/partners/European Union.png" alt="European Union Logo" />
            </div>
            <div class="partner-logo-card" title="Unitaid">
              <img src="img/partners/unitaid.png" alt="Unitaid Logo" />
            </div>
            <div class="partner-logo-card" title="Bill & Melinda Gates Foundation">
              <img src="img/partners/gate foundation.png" alt="Bill & Melinda Gates Foundation Logo" />
            </div>
            <div class="partner-logo-card" title="The Global Fund">
              <img src="img/partners/The-Global-Fund-Fighting-against-AIDS-tuberculosis-and-malaria.png"
                alt="The Global Fund Logo" />
            </div>
            <div class="partner-logo-card" title="GAVI Vaccine Alliance">
              <img src="img/partners/gavi.png" alt="GAVI Vaccine Alliance Logo" />
            </div>
            <div class="partner-logo-card" title="World Health Organization">
              <img src="img/partners/who.png" alt="World Health Organization Logo" />
            </div>
            <div class="partner-logo-card" title="Federal Ministry of Health Nigeria">
              <img src="img/partners/federal ministry of health.png"
                alt="Federal Ministry of Health Nigeria Logo" />
            </div>
            <div class="partner-logo-card" title="NACA Nigeria">
              <img src="img/partners/NACA.jpg" alt="NACA Logo" />
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script>
    // Server-filtered active jobs array
    const allJobs = <?php echo json_encode($activeJobsServer); ?>;

    function renderJobs(jobs) {
      const grid = document.getElementById('jobsGrid');
      grid.innerHTML = '';

      // Strict client-side verification as well: strictly filter active jobs
      const activeOnly = jobs.filter(j => (j.status || 'Active') === 'Active');

      if (activeOnly.length === 0) {
        grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 40px;">No active job vacancies currently open.</div>';
        return;
      }

      activeOnly.forEach(job => {
        const card = document.createElement('div');
        card.className = 'job-card';
        card.innerHTML = `
          <div>
            <div>
              <span class="job-dept-badge">${job.department}</span>
              <span class="contract-type-pill">${job.type}</span>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 800; margin: 10px 0;">${job.title}</h3>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">
              <span><i class="fa-solid fa-location-dot" style="color: var(--secondary-green);"></i> ${job.location}</span>
            </div>
            <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 16px;">${job.summary}</p>
          </div>
          <div style="display: flex; gap: 10px;">
            <a href="careers.html" class="btn-emerald" style="flex:1; justify-content: center;">Apply Now <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        `;
        grid.appendChild(card);
      });
    }

    function filterJobs() {
      const query = document.getElementById('searchInput').value.toLowerCase();
      const dept = document.getElementById('deptFilter').value;
      const loc = document.getElementById('locationFilter').value;
      const type = document.getElementById('typeFilter').value;

      const filtered = allJobs.filter(job => {
        const isActive = (job.status || 'Active') === 'Active';
        const matchesQuery = job.title.toLowerCase().includes(query) || job.summary.toLowerCase().includes(query);
        const matchesDept = !dept || job.department === dept;
        const matchesLoc = !loc || job.location.includes(loc);
        const matchesType = !type || job.type === type;
        return isActive && matchesQuery && matchesDept && matchesLoc && matchesType;
      });

      renderJobs(filtered);
    }

    renderJobs(allJobs);

    // Back to Top button functionality
    document.addEventListener('DOMContentLoaded', () => {
      const backToTopBtn = document.getElementById('backToTop');
      if (backToTopBtn) {
        const toggleBackToTop = () => {
          if (window.scrollY > 300) {
            backToTopBtn.classList.add('active');
          } else {
            backToTopBtn.classList.remove('active');
          }
        };

        window.addEventListener('scroll', toggleBackToTop, { passive: true });
        toggleBackToTop();

        backToTopBtn.addEventListener('click', () => {
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
        });
      }
    });
  </script>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop" aria-label="Back to Top" title="Back to Top">
  <i class="fa-solid fa-arrow-up"></i>
</button>
</body>

</html>
