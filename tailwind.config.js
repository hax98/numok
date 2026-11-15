/**
 * Repostit design system on Tailwind.
 * Token values come from the Repostit design system (tokens.json, synced from hax98/repostit-site).
 * The gray scale is remapped onto the system's neutrals so every existing gray-* class in the
 * views lands on a design-system color; new markup should prefer the semantic names (ink, line, ...).
 */
const ds = {
  ink: '#16121f',
  'ink-hover': '#2e2840',
  'ink-deep': '#2a1b3d',
  'text-body': '#2b2735',
  'text-secondary': '#5a5566',
  'text-muted': '#6e6879',
  'text-faint': '#8c8698',
  surface: '#ffffff',
  'surface-subtle': '#f7f6fa',
  'surface-hover': '#f6f5f9',
  'surface-code': '#f4f3f7',
  line: '#e4e1eb',
  'line-soft': '#eeecf2',
  'line-strong': '#cfc9db',
  'line-control': '#8c8698',
  brand: '#673de5',
  'brand-text': '#4a2bc2',
  'brand-tint': '#efebfd',
  'on-dark': '#f1ecf8',
  'on-dark-faint': '#c9c4d6',
  success: '#2f7a4c',
  'success-tint': '#e5f3ea',
  warning: '#a86a00',
  'warning-tint': '#fff6e6',
  danger: '#b4531f',
  'danger-tint': '#fbede4',
};

const gray = {
  50: ds['surface-subtle'],
  100: ds['surface-code'],
  200: ds.line,
  300: ds['line-strong'],
  400: ds['text-faint'],
  500: ds['text-muted'],
  600: ds['text-secondary'],
  700: ds['text-body'],
  800: ds['text-body'],
  900: ds.ink,
  950: ds.ink,
};

module.exports = {
  content: ['./src/Views/**/*.php', './public/assets/js/*.js'],
  theme: {
    extend: {
      colors: {
        ...ds,
        gray,
        // Status colors the existing views use, pulled onto the system's status pairs.
        green: { 50: ds['success-tint'], 100: ds['success-tint'], 200: ds.line, 300: ds.line, 400: ds.success, 500: ds.success, 600: ds.success, 700: ds.success, 800: ds.success },
        yellow: { 50: ds['warning-tint'], 100: ds['warning-tint'], 200: ds.line, 300: ds.line, 400: ds.warning, 500: ds.warning, 600: ds.warning, 700: ds.warning, 800: ds.warning },
        red: { 50: ds['danger-tint'], 100: ds['danger-tint'], 200: ds.line, 300: ds.line, 400: ds.danger, 500: ds.danger, 600: ds.danger, 700: ds.danger, 800: ds.danger },
        blue: { 50: '#e8f0fb', 100: '#e8f0fb', 200: ds.line, 400: '#1f5bb8', 500: '#1f5bb8', 600: '#1f5bb8', 800: '#1f5bb8' },
      },
      fontFamily: {
        sans: ['Geist', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
      },
      borderRadius: {
        DEFAULT: '10px',
        sm: '6px',
        md: '12px',
        lg: '14px',
        xl: '20px',
        '2xl': '28px',
      },
      // Separate things with borders, not shadows: card shadows become a 1px line.
      boxShadow: {
        DEFAULT: `0 0 0 1px ${ds.line}`,
        sm: 'none',
        md: `0 0 0 1px ${ds.line}`,
        lg: '0 28px 48px -28px rgba(22, 18, 31, 0.28)',
        xl: '0 28px 48px -28px rgba(22, 18, 31, 0.28)',
        menu: '0 28px 48px -28px rgba(22, 18, 31, 0.28)',
        float: '0 4px 12px rgba(22, 18, 31, 0.18)',
      },
      maxWidth: { page: '1192px', text: '760px' },
      height: { 'control-s': '44px', 'control-m': '48px', 'control-l': '52px' },
      minHeight: { 'control-s': '44px', 'control-m': '48px', 'control-l': '52px' },
      letterSpacing: { display: '-0.035em', 'display-l': '-0.03em', label: '0.06em' },
    },
  },
  plugins: [require('@tailwindcss/forms')],
};
