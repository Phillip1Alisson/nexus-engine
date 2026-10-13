import js from '@eslint/js';
import tseslint from 'typescript-eslint';

export default tseslint.config(
  js.configs.recommended,
  ...tseslint.configs.recommended,
  {
    files: ['**/*.ts', '**/*.tsx'],
    rules: {
      // Regra 1: Força o limite máximo de 200 linhas por arquivo
      'max-lines': ['error', { max: 200, skipBlankLines: true, skipComments: true }],
      
      // Regra 2: Controla a complexidade de aninhamentos e caminhos lógicos (No Nested Ifs)
      'complexity': ['error', { max: 3 }],
      
      // Regra 3: Limita o tamanho de uma única função (Máximo 15-20 linhas ideais)
      'max-lines-per-function': ['warn', { max: 25, skipBlankLines: true, skipComments: true }],
      
      // Regra 4: Limita a profundidade de blocos aninhados (máximo 2 níveis)
      'max-depth': ['error', { max: 2 }],

      // Boas práticas gerais para o TypeScript
      '@typescript-eslint/no-explicit-any': 'error', // Proíbe o uso de 'any'
      '@typescript-eslint/explicit-function-return-type': 'warn', // Força retorno tipado
      'no-console': ['warn', { allow: ['warn', 'error'] }], // Evita console.log perdidos
    },
  }
);
