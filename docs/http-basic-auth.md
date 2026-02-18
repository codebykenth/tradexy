# HTTP Basic Auth — Protect Dev & Staging Environments

> **Purpose:** Restrict access to `dev.tradexy.site` and `staging.tradexy.site` so only you can view them. Production (`tradexy.site`) remains fully public.
>
> **How it works:** Nginx prompts for a username and password before allowing access. This happens at the server level — no code changes required.

---

## Prerequisites

- SSH access to your server
- Host Nginx already configured (see [deployment-guide.md](../deployment-guide.md))

---

## Setup (One-Time)

### Step 1: SSH into your server

```bash
ssh -i ~/.ssh/<YOUR_KEY> root@<YOUR_SERVER_IP>
```

### Step 2: Install `htpasswd` utility

```bash
apt install -y apache2-utils
```

> `htpasswd` is a tool for creating username:password files. It's part of `apache2-utils` but works with any web server, including Nginx.

### Step 3: Create a password file

```bash
htpasswd -c /etc/nginx/.htpasswd <your_username>
```

You'll be prompted to enter and confirm a password. This creates the file `/etc/nginx/.htpasswd` with your encrypted credentials.

**To add more users later** (omit `-c` so you don't overwrite the file):
```bash
htpasswd /etc/nginx/.htpasswd another_user
```

**To verify the file was created:**
```bash
cat /etc/nginx/.htpasswd
# Output: yourname:$apr1$xyz...  (encrypted password)
```

### Step 4: Update Nginx config for Dev

```bash
nano /etc/nginx/sites-available/tradexy-dev
```

Add `auth_basic` and `auth_basic_user_file` inside the `location /` block:

```nginx
server {
    listen 80;
    server_name dev.tradexy.site;

    location / {
        auth_basic "Dev Environment";                # ← Prompt message
        auth_basic_user_file /etc/nginx/.htpasswd;   # ← Password file

        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
    client_max_body_size 64M;
}
```

> **Note:** If Certbot has already modified this file with SSL blocks, only add the two `auth_basic` lines inside the existing `location /` block. Don't replace the entire file.

### Step 5: Update Nginx config for Staging

```bash
nano /etc/nginx/sites-available/tradexy-staging
```

Same change — add the two `auth_basic` lines inside `location /`:

```nginx
auth_basic "Staging Environment";
auth_basic_user_file /etc/nginx/.htpasswd;
```

### Step 6: Test and reload Nginx

```bash
nginx -t                    # Test config for syntax errors
systemctl reload nginx      # Apply changes (zero downtime)
```

---

## Result

| Environment | URL | Access |
|---|---|---|
| **Dev** | `https://dev.tradexy.site` | 🔒 Username + password required |
| **Staging** | `https://staging.tradexy.site` | 🔒 Username + password required |
| **Production** | `https://tradexy.site` | 🌐 Public — no changes |

When visiting dev or staging, the browser will show a login popup:

```
┌──────────────────────────────────────┐
│  dev.tradexy.site requires           │
│  authentication                      │
│                                      │
│  Username: [__________]              │
│  Password: [__________]              │
│                                      │
│           [Cancel] [Sign in]         │
└──────────────────────────────────────┘
```

---

## Managing Users

**Add a new user:**
```bash
htpasswd /etc/nginx/.htpasswd newuser
```

**Remove a user:**
```bash
htpasswd -D /etc/nginx/.htpasswd olduser
```

**Change a user's password:**
```bash
htpasswd /etc/nginx/.htpasswd existinguser
```

**List all users:**
```bash
cat /etc/nginx/.htpasswd
```

After any change, reload Nginx:
```bash
systemctl reload nginx
```

---

## Disable Basic Auth (if needed)

To remove the protection, simply delete or comment out the two `auth_basic` lines:

```nginx
location / {
    # auth_basic "Dev Environment";
    # auth_basic_user_file /etc/nginx/.htpasswd;

    proxy_pass http://127.0.0.1:8080;
    # ...
}
```

Then reload:
```bash
nginx -t && systemctl reload nginx
```

---

## References

- [Nginx Official — Restricting Access with HTTP Basic Authentication](https://docs.nginx.com/nginx/admin-guide/security-controls/configuring-http-basic-authentication/)
- [Nginx Official — ngx_http_auth_basic_module](https://nginx.org/en/docs/http/ngx_http_auth_basic_module.html)
- [DigitalOcean — How To Set Up Password Authentication with Nginx](https://www.digitalocean.com/community/tutorials/how-to-set-up-password-authentication-with-nginx-on-ubuntu-22-04)
