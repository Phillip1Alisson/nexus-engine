import { defineConfig } from 'vitest/config';
import swc from 'unplugin-swc';

export default defineConfig({
  test: {
    globals: true,
    root: './',
    include: ['**/*.e2e-spec.ts', '**/*.spec.ts'],
  },
  plugins: [
    // Utiliza o SWC para compilar os arquivos TypeScript em tempo recorde nos testes
    swc.vite({
      module: { type: 'es2022' },
    }),
  ],
});
