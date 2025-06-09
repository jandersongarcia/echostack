import React from 'react';
import AccountForm from './AccountForm';
import { Box } from '@chakra-ui/react';

export default function AccountPage() {
  return (
    <Box minH="100vh" display="flex" alignItems="center" justifyContent="center">
      <AccountForm />
    </Box>
  );
}
