import type { Metadata } from 'next';
import { Outfit } from 'next/font/google';
import { SiteFooter } from '@/components/SiteFooter';
import { SiteHeader } from '@/components/SiteHeader';
import './globals.css';

const outfit = Outfit({
  subsets: ['latin-ext'],
  variable: '--font-outfit',
});

export const metadata: Metadata = {
  title: 'Çekirdex — Restoran ve oteller için sadakatin yeni çekirdeği',
  description:
    "Çekirdex; QR'dan ödeme, sadakat puanı, operasyon yönetimi ve raporlamayı tek platformda toplar.",
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="tr" className={outfit.variable}>
      <body>
        <SiteHeader />
        {children}
        <SiteFooter />
      </body>
    </html>
  );
}
