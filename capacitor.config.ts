import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'fr.mondary.tarotdivinatoire',
  appName: 'Tarot Divinatoire',
  webDir: process.env.CAP_PLATFORM === 'ios' ? 'src/mobile/www' : 'Android/www',
  bundledWebRuntime: false,
  ios: { contentInset: 'always' },
  android: { path: 'Android', allowMixedContent: false, adjustMarginsForEdgeToEdge: 'force', backgroundColor: '#101d2b' }
};

export default config;
