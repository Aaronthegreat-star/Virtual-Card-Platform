FROM node:lts-alpine AS builder
WORKDIR /app


RUN apk add --no-cache \
    python3 \
    make \
    g++ 

COPY websocket/package*.json ./


RUN npm ci --omit=dev && npm cache clean --force

COPY websocket/ /app/ 


FROM node:lts-alpine AS runtime
WORKDIR /app


USER node

COPY --from=builder --chown=node:node /app ./

ENV NODE_ENV=production


EXPOSE 8080


CMD ["node", "index.js"]