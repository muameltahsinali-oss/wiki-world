'use client';

export default function GlobalError({
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  return (
    <html lang="en">
      <body className="flex min-h-screen items-center justify-center bg-[#fafaf8] p-6 text-[#1a1d26]">
        <main className="flex max-w-md flex-col items-center gap-4 text-center">
          <h1 className="text-2xl font-semibold">Something went wrong</h1>
          <p className="text-sm text-[#4b5563]">
            A critical error occurred. Please try again or return to the homepage.
          </p>
          <button
            type="button"
            onClick={reset}
            className="rounded-md bg-[#1b6b6b] px-4 py-2 text-sm text-white"
          >
            Try again
          </button>
        </main>
      </body>
    </html>
  );
}
