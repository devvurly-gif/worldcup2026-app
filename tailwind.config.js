/** @type {import('tailwindcss').Config} */
export default {
  content: ['./resources/**/*.{vue,js,blade.php}'],
  theme: {
    extend: {
      colors: {
        brand: { DEFAULT: '#f59e0b', dark: '#d97706', light: '#fbbf24' },
        navy:  { DEFAULT: '#0a0a2e', mid: '#1a1a5e', deep: '#050518' },
      },
      fontFamily: { sans: ['Inter', 'sans-serif'] },
      animation: {
        'pulse-slow': 'pulse 3s ease-in-out infinite',
        'blink': 'blink 1s step-end infinite',
        'shimmer': 'shimmer 2s ease-in-out infinite',
      },
      keyframes: {
        blink: { '0%,100%': { opacity: 1 }, '50%': { opacity: 0 } },
        shimmer: { '0%,100%': { opacity: 1 }, '50%': { opacity: 0.5 } },
      },
    },
  },
  plugins: [require('daisyui')],
  daisyui: {
    themes: [
      {
        wc2026: {
          'primary':          '#f59e0b',
          'primary-content':  '#000000',
          'secondary':        '#1e3a5f',
          'secondary-content':'#ffffff',
          'accent':           '#ef4444',
          'accent-content':   '#ffffff',
          'neutral':          '#0a0f1e',
          'neutral-content':  '#ffffff',
          'base-100':         '#0d1b2a',
          'base-200':         '#0a1520',
          'base-300':         '#071018',
          'base-content':     '#e2e8f0',
          'info':             '#3b82f6',
          'success':          '#22c55e',
          'warning':          '#f59e0b',
          'error':            '#ef4444',
        },
      },
    ],
    darkTheme: 'wc2026',
    logs: false,
  },
}
