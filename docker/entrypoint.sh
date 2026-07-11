# #!/bin/sh
# set -e

# service nginx start
# exec "$@"
#!/bin/sh
#!/bin/sh
set -e

php-fpm -D
exec nginx -g 'daemon off;'