FROM php:5.4.45-cli
ENV CC=gcc
ENV CFLAGS="-g -O2 -std=gnu99"
WORKDIR /usr/src/
COPY php.ini /usr/local/etc/php/
COPY ZendGuardLoader.so .
COPY Zend-Decoder /usr/src/Zend-Decoder
RUN apt-get update && apt-get install -y --force-yes git patch
RUN git clone https://github.com/lighttpd/xcache
RUN cd xcache && \
    patch -p1 < ../Zend-Decoder/xcache.patch && \
    phpize && \
    ./configure --enable-xcache-disassembler && \
    make
