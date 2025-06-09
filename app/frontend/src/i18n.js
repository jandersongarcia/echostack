import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';
import ptBR from './locales/pt-BR/translation.json';

i18n
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    fallbackLng: 'pt-BR',
    debug: true,
    interpolation: {
      escapeValue: false
    },
    resources: {
      'pt-BR': { translation: ptBR }
    }
  });

export default i18n;
