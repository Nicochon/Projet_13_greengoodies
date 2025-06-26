import { defineConfig } from 'vite'
import { viteExternalsPlugin } from 'vite-plugin-externals'
import { fileURLToPath } from 'url';
import compileTime from "vite-plugin-compile-time"

export default defineConfig({
  plugins: [
    compileTime(),
    viteExternalsPlugin({   
        prestashop: 'prestashop',
        $: '$',
        jquery: 'jQuery'
    }),
  ],
  base: './',
  build:{
    manifest: true,
    outDir: "../assets/",
    emptyOutDir: false, 
    rollupOptions: {
        input: {
            "theme": "./js/theme.js",
            "css": "./css/theme.scss"
          },
          output: {
            assetFileNames: (assetInfo) => {
              let extType = assetInfo.name
              let fileExtension = extType.substring(extType.lastIndexOf('.') + 1);
              if (/woff|ttf|svg/i.test(fileExtension)) {
                return 'fonts/[name][extname]';
              }
              return '[ext]/[name][extname]';
            },
            entryFileNames: (chunkInfo) => {
                return 'js/[name].js';
            },
            
          },
    },
  },
  server: {
    origin: '.',
    watch: {
      '**/*.js': true,
      '**/*.sass': true,
    }
    // hmr: {
    //   host: 'vue.prestasafe.com',
    //   clientPort: 443,
    //   protocol: 'wss'
    // } 
    // hmr: {
    //   host: 'localhost',
    //   protocol: 'ws'
    // }
  }
})