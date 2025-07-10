const { defineConfig } = require("cypress");
const path = require("path");
const vue = require("@vitejs/plugin-vue");

module.exports = defineConfig({
  component: {
    devServer: {
      framework: "vue",
      bundler: "vite",
      viteConfig: {
        plugins: [vue()],
        resolve: {
          alias: {
            "@": path.resolve(__dirname, "src"),
            "@/services/websocket": path.resolve(
              __dirname,
              "cypress/support/mocks/mock-websocket.ts"
            ),
            "@/services/IsConnected": path.resolve(
              __dirname,
              "cypress/support/mocks/IsConnected.js"
            ),
          },
        },
      },
    },
  },

  e2e: {
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
});
