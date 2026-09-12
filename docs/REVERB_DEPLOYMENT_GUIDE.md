# Guide de Déploiement Laravel Reverb (Production)

Ce guide détaille la configuration nécessaire pour faire fonctionner les WebSockets avec **Laravel Reverb** sur un serveur de production (type VPS Hostinger, Ubuntu/Nginx).

> [!IMPORTANT]
> Laravel Reverb nécessite un **VPS**. Il ne fonctionnera pas sur un hébergement mutualisé standard car il nécessite un processus en arrière-plan permanent.

---

## 1. Configuration du fichier `.env`

En production, nous utilisons le port standard HTTPS (443) pour éviter les blocages de pare-feu chez les utilisateurs.

```env
# Configuration Reverb
BROADCAST_DRIVER=reverb

REVERB_APP_ID=196113
REVERB_APP_KEY=oseylu5sd3axnur0phhu
REVERB_APP_SECRET=jvs3oiyffu8ps54px9ii

# En production, utilisez votre domaine réel
REVERB_HOST="votre-domaine.com"
REVERB_PORT=443
REVERB_SCHEME=https

# Configuration pour le Frontend (Nuxt/Vite)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

---

## 2. Configuration du Reverse Proxy (Nginx)

Comme Reverb tourne en interne sur le port 8080, Nginx doit rediriger le trafic WebSocket sécurisé vers lui.

Ajoutez ce bloc dans votre fichier de configuration Nginx (souvent dans `/etc/nginx/sites-available/votre-site`) :

```nginx
server {
    # ... vos configurations SSL existantes ...

    location /app {
        proxy_http_version 1.1;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";

        proxy_pass http://127.0.0.1:8080;
    }
}
```

N'oubliez pas de redémarrer Nginx : `sudo systemctl restart nginx`

---

## 3. Gestion du processus avec Supervisor

Pour que Reverb ne s'arrête jamais, utilisez Supervisor.

1. Créez le fichier : `sudo nano /etc/supervisor/conf.d/reverb.conf`
2. Collez la configuration suivante (adaptez les chemins) :

```ini
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/votre-projet/artisan reverb:start --host=127.0.0.1 --port=8080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/votre-projet/storage/logs/reverb.log
stopwaitsecs=3600
```

3. Activez-le :
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb:*
```

---

## 4. Pare-feu (UFW)

Si vous avez un pare-feu actif, assurez-vous que les ports 80 et 443 sont ouverts. Le port 8080 n'a pas besoin d'être ouvert au public car Nginx communique avec lui en local (`127.0.0.1`).

```bash
sudo ufw allow 'Nginx Full'
```

---

## 5. Résumé des commandes utiles

- **Démarrer manuellement (pour test) :**  
  `php artisan reverb:start --debug`
- **Vérifier le statut Supervisor :**  
  `sudo supervisorctl status`
- **Consulter les logs Reverb :**  
  `tail -f storage/logs/reverb.log`
