import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
export default defineConfig({
  plugins: [tailwindcss()],
  css: {
    modules: {
      scopeBehaviour: "local",
      localsConvention: "camelCase",
      generateScopedName: "[local]_[hash:base64:20]",
    },
  },
  build: {
    target: "es2022",
    cssCodeSplit: false,
  },
});