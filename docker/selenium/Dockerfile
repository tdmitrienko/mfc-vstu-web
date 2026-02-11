FROM selenium/standalone-chrome:4.20.0-20240425

ARG USER_ID
ARG GROUP_ID
ARG USER_NAME=seluser
ARG GROUP_NAME=seluser

USER root

RUN groupmod -g $GROUP_ID $GROUP_NAME && \
    usermod -u $USER_ID -g $GROUP_ID $USER_NAME && \
    mkdir -p /home/$USER_NAME/Downloads && \
    chown -R $USER_ID:$GROUP_ID /home/$USER_NAME/Downloads

RUN mkdir -p /var/log/supervisor && \
    chown -R $USER_ID:$GROUP_ID /var/log/supervisor

USER $USER_NAME