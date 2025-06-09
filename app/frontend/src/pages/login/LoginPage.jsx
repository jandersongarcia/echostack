import React from 'react';
import LoginForm from './LoginForm';
import { Box } from '@chakra-ui/react';

export default function LoginPage() {
  return (
    <Box minH="100vh" display="flex" alignItems="center" justifyContent="center">
      <LoginForm />
    </Box>
  );
}
