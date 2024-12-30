FROM node:20.11

RUN yarn config set cache-folder /app/.yarn-cache

WORKDIR /app

CMD yarn install && yarn run dev
