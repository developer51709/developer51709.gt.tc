# developer51709.gt.tc  
**Official source code for the Developer51709 website**

This repository contains the full source code for **https://developer51709.gt.tc**, the personal website of Developer51709.  
The site hosts projects, APIs, documentation, and general information about development work.

---

## 🚀 About the Website

The website serves as a central hub for:

- **Project pages**  
- **API documentation**  
- **Developer tools and utilities**  
- **Contact and community links**  
- **Static HTML content** hosted on InfinityFree

The site is lightweight, fast, and built entirely with static HTML/CSS/JS for maximum reliability and portability.

---

## 📁 Repository Structure

```Code
/
├── index.html
├── projects/
├── apis/
├── assets/
├── .github/workflows/
│   └── sync.yml      # GitHub Actions workflow for FTP sync
└── README.md
```

- **HTML files** make up the main pages of the site  
- **Assets** include images, stylesheets, and scripts  
- **GitHub Actions** automates deployment and syncing with the live server

---

## 🔄 Deployment & Syncing

This repository includes a GitHub Actions workflow that can:

- **Push** local changes → InfinityFree hosting  
- **Pull** remote changes → GitHub repository  

The workflow is triggered manually using `workflow_dispatch` and supports two modes:

- `push` — upload files to the server  
- `pull` — download files from the server (using an FTP mirror command)

Secrets required:

- `FTP_SERVER`
- `FTP_USERNAME`
- `FTP_PASSWORD`

---

## 🛠 Technologies Used

- **HTML5 / CSS3 / JavaScript**
- **Static hosting on InfinityFree**
- **GitHub Actions** for automation
- **lftp** (for pull mode)
- **FTP-Deploy-Action** (for push mode)

---

## 📬 Contact & Links

- **Website:** https://developer51709.gt.tc  
- **GitHub:** https://github.com/developer51709  
- **Discord:** (link available on the website)

---

## 📄 License

This project is open source.  
You may view, fork, or contribute according to the repository’s license terms (if provided).

---

## 🤝 Contributing

Contributions, suggestions, and improvements are welcome!  
Feel free to open an issue or submit a pull request.
