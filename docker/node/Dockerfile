FROM node:20-alpine

# Set working directory
WORKDIR /app

# Install app dependencies
# A wildcard is used to ensure both package.json AND package-lock.json are copied
COPY package*.json ./

# Install dependencies (including dev dependencies for development container)
RUN npm ci

# Bundle app source
COPY . .

# Create user and change ownership
RUN addgroup -g 1001 -S nodejs
RUN adduser -S reactuser -u 1001
RUN chown -R reactuser:nodejs /app
USER reactuser

# Expose port
EXPOSE 5173

# Start the application
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"] 