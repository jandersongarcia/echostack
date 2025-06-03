import React from 'react';
import { Trans, useTranslation } from 'react-i18next';
import { Typography, Link } from '@mui/material';

export default function TermsText({ openTerms, openServices, openPrivacy }) {
  const { t } = useTranslation();

  return (
    <Typography variant="body2" color="textSecondary" mt={1}>
      <Trans i18nKey="terms_text"
        components={{
          terms: <Link component="button" type="button" onClick={openTerms} />,
          services: <Link component="button" type="button" onClick={openServices} />,
          privacy: <Link component="button" type="button" onClick={openPrivacy} />
        }}
      />
    </Typography>
  );
}
