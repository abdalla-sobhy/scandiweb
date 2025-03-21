FROM mysql:8.0

# Copy initialization scripts (if any)
COPY ./api/config /docker-entrypoint-initdb.d

# Expose MySQL port
EXPOSE 3306
