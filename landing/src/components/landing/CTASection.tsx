import { Button } from '@/components/ui/button';
import { ArrowRight, Sparkles } from 'lucide-react';
import siteData from '@/data/siteData.json';

export function CTASection() {
  return (
    <section className="py-24 relative overflow-hidden">
      {/* Background Effects */}
      <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-secondary/5 to-accent/5" />
      <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-primary/10 rounded-full blur-3xl" />
      
      <div className="container mx-auto px-4 sm:px-6 relative z-10">
        <div className="max-w-4xl mx-auto">
          <div className="glass-card rounded-[2rem] md:rounded-[3rem] p-8 md:p-16 text-center relative overflow-hidden">
            {/* Decorative Elements */}
            <div className="absolute top-0 left-0 w-32 h-32 bg-gradient-to-br from-primary/20 to-transparent rounded-br-full" />
            <div className="absolute bottom-0 right-0 w-32 h-32 bg-gradient-to-tl from-secondary/20 to-transparent rounded-tl-full" />

            {/* Content */}
            <div className="relative z-10">
              <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 border border-primary/20 mb-8">
                <Sparkles className="w-4 h-4 text-primary" />
                <span className="text-sm font-medium text-primary">
                  Mulai Sekarang
                </span>
              </div>

              <h2 className="text-3xl sm:text-4xl md:text-5xl font-bold mb-6">
                <span className="gradient-text">{siteData.cta.title}</span>
              </h2>

              <p className="text-lg text-muted-foreground max-w-2xl mx-auto mb-10">
                {siteData.cta.description}
              </p>

              <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
                <Button variant="gradient" size="xl" className="w-full sm:w-auto group">
                  {siteData.cta.button}
                  <ArrowRight className="w-5 h-5 transition-transform group-hover:translate-x-1" />
                </Button>
                <Button variant="hero" size="xl" className="w-full sm:w-auto">
                  Lihat Dokumentasi
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
