# GitHub submission – real steps, not simulated

## Before Git

1. Test the XAMPP site locally.
2. Never commit `.env`, real exported database snapshots, password hashes, session cookies, API keys, actual user uploads or screenshots containing private user data to a public repository.
3. `.gitignore` excludes `database/backups/`, `submission/private/`, actual uploaded event posters and screenshots. Verify your working directory contains only intended files.

## Git setup (PowerShell, Git installed)

```powershell
cd C:\xampp\htdocs\StudentHub_MySQL_Practical_15
git init
git branch -M main
git add .
git status
git diff --cached --stat
git commit -m "Integrate Lumiora StudentHub Practical 15 modules"
```

Review `git status` carefully before commit.

## Publish (your own GitHub account)

1. On GitHub, **create a new empty repository**, for example `lumiora-studenthub-final`. Do not initialize it with another README if pushing this repository directly.
2. Copy your actual GitHub HTTPS remote URL and run:

```powershell
git remote add origin https://github.com/YOUR-USERNAME/lumiora-studenthub-final.git
git push -u origin main
```

3. If Git asks for authentication, use Git Credential Manager or an appropriate personal access token; do **not** paste a token into chat or source files.
4. Open your real GitHub repo URL, verify `README.md`, module files and commit history. Put the **real link** in Teams/MS submission. Do not invent a URL.

## Submission package

- Public/private repo link (according to instructor guidance)
- Local XAMPP screenshots and the `docs/TEST_REPORT.md` filled from actual browser evidence
- Full private SQL dump from `studenthub_pr8_db` uploaded **privately** to the submission platform where allowed; it includes sensitive fields and must not go in a public GitHub repo
- Screenshots of `git status`, `git log --oneline -3`, and GitHub repository page
- Recorded individual demo and local virtual host evidence if completed

Static GitHub Pages can host HTML/CSS/JS only; it **cannot execute PHP/MySQL**. Do not claim GitHub Pages hosts this PHP backend. For live deployment use a real PHP/MySQL-compatible host with proper security or document the limitation as an advanced extension.
