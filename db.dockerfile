FROM mysql:8.0

# Copy initialization scripts (if any)
COPY ./api/migrations /docker-entrypoint-initdb.d

# Expose MySQL port
EXPOSE 3306
