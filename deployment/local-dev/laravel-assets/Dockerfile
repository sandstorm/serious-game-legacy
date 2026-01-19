FROM node:24.11-slim

RUN yarn config set cache-folder /app/.yarn-cache

WORKDIR /app

CMD yarn install && yarn run dev
