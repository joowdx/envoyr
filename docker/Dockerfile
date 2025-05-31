FROM ubuntu:24.04

LABEL maintainer="Joowdx"

ARG WWWGROUP

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Manila
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::BrokenProxy    true;" >> /etc/apt/apt.conf.d/99custom

RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python3 dnsutils librsvg2-bin fswatch ffmpeg nano  \
       php-cli php-dev \
       php-pgsql php-sqlite3 php-gd \
       php-curl php-mongodb \
       php-imap php-mysql php-mbstring \
       php-xml php-zip php-bcmath php-soap \
       php-intl php-readline \
       php-ldap \
       php-msgpack php-igbinary php-redis \
       php-memcached php-pcov php-imagick php-xdebug \
       composer nodejs npm  \
       mysql-client \
       libx11-xcb1 libxcomposite1 libasound2t64 libatk1.0-0 libatk-bridge2.0-0 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgbm1 libgcc1 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.3

RUN userdel -r ubuntu
RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail
RUN npm -g i n && n stable && n prune && npm install -g puppeteer
RUN su - sail -c "npx puppeteer browsers install chrome"

COPY --from=phpswoole/swoole:php8.3 /usr/local/lib/php/extensions/no-debug-non-zts-20230831/swoole.so /usr/lib/php/20230831/
COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.3/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container
RUN echo "extension=swoole.so" > /etc/php/8.3/cli/conf.d/30-swoole.ini

EXPOSE 80/tcp

ENTRYPOINT ["start-container"]
