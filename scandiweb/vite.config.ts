import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
export default defineConfig({
  plugins: [tailwindcss()],
  css: {
    modules: {
      scopeBehaviour: "local",
      localsConvention: "camelCase",
      generateScopedName: "[local]",
    },
  },
  build: {
    target: "es2022",
    cssCodeSplit: false,
  },
  server: {
    cors: {
      origin: "*",
      credentials: true,
      methods: ["GET", "POST", "PUT", "DELETE"],
    },
  },
});
