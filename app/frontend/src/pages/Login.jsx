import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import NavbarLogin from '../components/NavbarLogin';
import { Box, Button, TextField, Typography, Divider, Stack } from '@mui/material';

export default function Login() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log('Email:', email, 'Senha:', password);
  };

  return (
    <Box sx={{ fontFamily: 'Poppins, sans-serif', minHeight: '100vh', display: 'flex', flexDirection: 'column' }}>
      <NavbarLogin />

      <Box flex={1} display="flex" justifyContent="center" alignItems="center">
        <Box width="100%" maxWidth="400px" textAlign="center">

          <Typography variant="h5" mb={3}>{t('title')}</Typography>

          <Button fullWidth variant="outlined" sx={{ mb: 3 }}>
            {t('continue_google')}
          </Button>

          <Stack direction="row" alignItems="center" spacing={2} mb={3}>
            <Divider sx={{ flex: 1 }} />
            <Typography variant="body2" color="textSecondary">{t('or')}</Typography>
            <Divider sx={{ flex: 1 }} />
          </Stack>

          <form onSubmit={handleSubmit}>
            <Box display="flex" flexDirection="column" gap={2}>

              <TextField
                fullWidth
                type="email"
                placeholder={t('email')}
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
              />

              <TextField
                fullWidth
                type="password"
                placeholder={t('password')}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />

              <Button type="submit" fullWidth variant="contained" size="large" sx={{ borderRadius: 2 }}>
                {t('login')}
              </Button>
            </Box>
          </form>

          <Typography variant="body2" mt={3}>
            {t('no_account')}
            <Box component="span" onClick={() => navigate('/register')} sx={{ color: '#0061FF', cursor: 'pointer', fontWeight: 'bold', ml: 0.5 }}>
              {t('register_link')}
            </Box>
          </Typography>

        </Box>
      </Box>
    </Box>
  );
}
