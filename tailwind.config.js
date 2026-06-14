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
        'wc2026-light': {
          'primary':          '#f97316',
          'primary-content':  '#ffffff',
          'secondary':        '#1e3a5f',
          'secondary-content':'#ffffff',
          'accent':           '#ea6b00',
          'accent-content':   '#ffffff',
          'neutral':          '#e2e8f0',
          'neutral-content':  '#1e293b',
          'base-100':         '#f8fafc',
          'base-200':         '#f1f5f9',
          'base-300':         '#e2e8f0',
          'base-content':     '#0f172a',
          'info':             '#3b82f6',
          'success':          '#22c55e',
          'warning':          '#f97316',
          'error':            '#ef4444',
        },
      },
      {
        wc2026: {
          'primary':          '#f97316',
          'primary-content':  '#ffffff',
          'secondary':        '#1e3a5f',
          'secondary-content':'#ffffff',
          'accent':           '#fb923c',
          'accent-content':   '#ffffff',
          'neutral':          '#0a0f1e',
          'neutral-content':  '#ffffff',
          'base-100':         '#050d1a',
          'base-200':         '#071226',
          'base-300':         '#0a1830',
          'base-content':     '#f8fafc',
          'info':             '#3b82f6',
          'success':          '#22c55e',
          'warning':          '#f97316',
          'error':            '#ef4444',
        },
      },
    ],
    darkTheme: 'wc2026',
    logs: false,
  },
}
