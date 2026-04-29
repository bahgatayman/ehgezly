# Git Essential Commands Cheat Sheet

## 🔵 Setup

git init git clone `<repo-url>`{=html}

## 👤 Config (first time only)

git config --global user.name "Your Name" git config --global user.email
"you@example.com"

## 📌 Status

git status

## ➕ Add changes

git add . git add `<file>`{=html}

## 💾 Commit

git commit -m "your message"

## 🌿 Branches

git branch git branch `<branch-name>`{=html} git checkout
`<branch-name>`{=html} git switch `<branch-name>`{=html}

## 🔀 Merge

git checkout main git merge `<branch-name>`{=html}

## 🚀 Push & Pull

git push origin `<branch-name>`{=html} git pull origin
`<branch-name>`{=html} git push -u origin `<branch-name>`{=html}

## 🏷 Tags (Backup points)

git tag v1.0 git push origin v1.0

## 🌍 Remote

git remote -v git remote add origin `<repo-url>`{=html}

## 🔙 Undo

git restore `<file>`{=html} git reset --hard git revert
`<commit-id>`{=html}

## 📜 Logs

git log git log --oneline

## 🧠 Useful Flow

1.  git checkout -b feature-name
2.  make changes
3.  git add .
4.  git commit -m "message"
5.  git push origin feature-name
6.  create Pull Request


## make versions of project
git tag v1.0
git push origin v1.0