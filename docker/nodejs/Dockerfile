FROM node:22-alpine

RUN apk add --no-cache --virtual .gyp  \
    curl \
    python3 make g++ \
    && apk del .gyp

ENV YARN_VERSION 1.22.19

RUN apk add --no-cache --virtual .build-deps-yarn curl \
    && curl -fSLO --compressed "https://yarnpkg.com/downloads/$YARN_VERSION/yarn-v$YARN_VERSION.tar.gz" \
    && tar -xzf yarn-v$YARN_VERSION.tar.gz -C /opt/ \
    && ln -snf /opt/yarn-v$YARN_VERSION/bin/yarn /usr/local/bin/yarn \
    && ln -snf /opt/yarn-v$YARN_VERSION/bin/yarnpkg /usr/local/bin/yarnpkg \
    && rm yarn-v$YARN_VERSION.tar.gz \
    && apk del .build-deps-yarn

ARG USER_ID
ARG GROUP_ID
ARG USER_NAME=node
ARG GROUP_NAME=node

RUN    sed -i "s/$USER_NAME:x:1000:1000/$USER_NAME:x:$USER_ID:$GROUP_ID/g" /etc/passwd  \
    && sed -i "s/$GROUP_NAME:x:1000:$GROUP_NAME/$GROUP_NAME:x:$GROUP_ID:$GROUP_NAME/g" /etc/group

COPY ./entrypoint.sh /
RUN sed -i 's/\r$//' /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER $USER_NAME
WORKDIR /var/www

ENTRYPOINT ["/bin/sh", "/entrypoint.sh"]
