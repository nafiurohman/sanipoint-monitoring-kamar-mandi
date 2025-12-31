import { Navbar } from '@/components/landing/Navbar';
import { HeroSection } from '@/components/landing/HeroSection';
import { FeaturesSection } from '@/components/landing/FeaturesSection';
import { HowItWorksSection } from '@/components/landing/HowItWorksSection';
import { TechnologySection } from '@/components/landing/TechnologySection';
import { TestimonialsSection } from '@/components/landing/TestimonialsSection';
import { CTASection } from '@/components/landing/CTASection';
import { Footer } from '@/components/landing/Footer';
import siteData from '@/data/siteData.json';

const Index = () => {
  return (
    <>
      {/* SEO Meta Tags */}
      <title>{siteData.seo.title}</title>
      <meta name="description" content={siteData.seo.description} />
      <meta name="keywords" content={siteData.seo.keywords.join(', ')} />
      
      <div className="min-h-screen bg-background scrollbar-thin">
        <Navbar />
        <main>
          <HeroSection />
          <FeaturesSection />
          <HowItWorksSection />
          <TechnologySection />
          <TestimonialsSection />
          <CTASection />
        </main>
        <Footer />
      </div>
    </>
  );
};

export default Index;
