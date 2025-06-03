import React from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import NavbarLogin from '../components/NavbarLogin';

export default function Login() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  return (
    <div style={{ fontFamily: 'Poppins, sans-serif', height: '100vh', position: 'relative' }}>
      <NavbarLogin />

      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100%' }}>
        <div style={{ width: '300px', textAlign: 'center' }}>
          <h2>{t('title')}</h2>

          <button style={{ width: '100%', marginBottom: '10px' }}>{t('continue_apple')}</button>

          <div style={{
            display: 'flex',
            alignItems: 'center',
            margin: '20px 0'
          }}>
            <div style={{ flex: 1, height: '1px', backgroundColor: '#ccc' }}></div>
            <span style={{ margin: '0 10px', color: '#666' }}>{t('or')}</span>
            <div style={{ flex: 1, height: '1px', backgroundColor: '#ccc' }}></div>
          </div>

          <input type="email" placeholder={t('email')} style={{ width: '100%', maxWidth: '90%', padding: '10px', marginBottom: '10px' }} />
          <input type="password" placeholder={t('password')} style={{ width: '100%', maxWidth: '90%', padding: '10px', marginBottom: '10px' }} />
          <button style={{ width: '98%', padding: '10px', paddingTop: '14px', paddingBottom: '14px', background: '#0061FF', color: 'white', border: 'none', borderRadius: '5px', cursor: 'pointer' }}>
            {t('login')}
          </button>

          <p style={{ marginTop: '20px' }}>
            {t('no_account')}
            <span
              onClick={() => navigate('/register')}
              style={{ color: '#0061FF', cursor: 'pointer', fontWeight: 'bold', marginLeft: '3px' }}
            >
              {t('register_link')}
            </span>
          </p>

        </div>
      </div>
    </div>
  );
}
