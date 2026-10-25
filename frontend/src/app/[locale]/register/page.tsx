'use client';

import { zodResolver } from '@hookform/resolvers/zod';
import { useLocale, useTranslations } from 'next-intl';
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Link, useRouter } from '@/i18n/navigation';
import { ApiClientError } from '@/lib/api/client';
import { register as registerUser } from '@/lib/api/endpoints';

const registerSchema = z
  .object({
    email: z.string().email(),
    password: z.string().min(8),
    password_confirmation: z.string().min(8),
    preferred_locale: z.enum(['ar', 'en']),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'passwordMismatch',
    path: ['password_confirmation'],
  });

type RegisterForm = z.infer<typeof registerSchema>;

export default function RegisterPage() {
  const t = useTranslations('auth');
  const errors = useTranslations('errors');
  const locale = useLocale();
  const router = useRouter();
  const [formError, setFormError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors: fieldErrors, isSubmitting },
  } = useForm<RegisterForm>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      preferred_locale: locale as 'ar' | 'en',
    },
  });

  const onSubmit = async (values: RegisterForm) => {
    setFormError(null);
    try {
      await registerUser(values, locale);
      setSuccess(true);
      router.push('/login');
    } catch (error) {
      if (error instanceof ApiClientError) {
        setFormError(error.message || errors('generic'));
      } else {
        setFormError(errors('generic'));
      }
    }
  };

  return (
    <main className="mx-auto flex w-full max-w-md flex-col gap-6 px-4 py-12 sm:px-6">
      <Card>
        <CardHeader>
          <CardTitle>{t('registerTitle')}</CardTitle>
          <CardDescription>{t('registerTitle')}</CardDescription>
        </CardHeader>
        <CardContent>
          {success ? <Alert variant="success">{t('registerSuccess')}</Alert> : null}
          <form onSubmit={handleSubmit(onSubmit)} className="flex flex-col gap-4">
            {formError ? <Alert variant="error">{formError}</Alert> : null}
            <Input
              label={t('email')}
              type="email"
              autoComplete="email"
              error={fieldErrors.email ? t('emailRequired') : undefined}
              {...register('email')}
            />
            <Input
              label={t('password')}
              type="password"
              autoComplete="new-password"
              error={fieldErrors.password ? t('passwordRequired') : undefined}
              {...register('password')}
            />
            <Input
              label={t('passwordConfirmation')}
              type="password"
              autoComplete="new-password"
              error={
                fieldErrors.password_confirmation ? t('passwordMismatch') : undefined
              }
              {...register('password_confirmation')}
            />
            <input type="hidden" {...register('preferred_locale')} />
            <Button type="submit" loading={isSubmitting} className="w-full">
              {t('submitRegister')}
            </Button>
          </form>
          <p className="mt-4 text-center text-sm text-[var(--color-ink-secondary)]">
            {t('hasAccount')}{' '}
            <Link href="/login" className="text-[var(--color-scholar)] hover:underline">
              {t('submitLogin')}
            </Link>
          </p>
        </CardContent>
      </Card>
    </main>
  );
}
