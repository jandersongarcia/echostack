import React from 'react';
import {
  Box, Button, FormControl, FormLabel, Heading, Input, Stack, Text, Center, Link
} from '@chakra-ui/react';
import { EmailIcon } from '@chakra-ui/icons';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { Link as RouterLink } from 'react-router-dom';

export default function LoginRecoveryPage() {
  const { register, handleSubmit } = useForm();
  const { t } = useTranslation();

  const onSubmit = (data) => {
    console.log(data);
  };

  return (
    <Box maxW="md" mx="auto" mt={10} p={8} borderWidth={1} borderRadius="lg" boxShadow="lg">
      <Heading mb={6} textAlign="center">{t('recoveryTitle')}</Heading>

      <Center mb={4}>
        <EmailIcon boxSize={16} color="yellow.400" />
      </Center>

      <Text fontSize="lg" textAlign="center" mb={2}>{t('recoveryEmailTitle')}</Text>

      <Text fontSize="sm" textAlign="center" mb={6} color="gray.600">
        {t('recoveryDescription')}
      </Text>

      <form onSubmit={handleSubmit(onSubmit)}>
        <Stack spacing={4}>
          <FormControl>
            <FormLabel>{t('email')}</FormLabel>
            <Input type="email" {...register('email')} />
          </FormControl>

          <Button type="submit" colorScheme="teal" size="lg">
            {t('startButton')}
          </Button>

          <Text textAlign="center" fontSize="sm">
            <Link as={RouterLink} to="/" color="teal.500">{t('backToLogin')}</Link>
          </Text>
          
        </Stack>
      </form>
    </Box>
  );
}
