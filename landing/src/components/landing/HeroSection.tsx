import { Button } from '@/components/ui/button';
import { ArrowRight, Play, Activity, Users, Zap, MapPin } from 'lucide-react';
import siteData from '@/data/siteData.json';

const iconMap: Record<string, React.ReactNode> = {
  'Uptime Sistem': <Activity className="w-5 h-5 text-primary" />,
  'Sensor Aktif': <Zap className="w-5 h-5 text-secondary" />,
  'Poin Didistribusikan': <Users className="w-5 h-5 text-accent" />,
  'Lokasi Terhubung': <MapPin className="w-5 h-5 text-success" />,
};

export function HeroSection() {
  return (
    <section className="relative min-h-screen flex items-center pt-20 overflow-hidden">
      {/* Background Effects */}
      <div className="absolute inset-0 grid-pattern" />
      <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/10 rounded-full blur-3xl" />
      <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-secondary/10 rounded-full blur-3xl" />
      
      <div className="container mx-auto px-4 sm:px-6 relative z-10">
        <div className="max-w-4xl mx-auto text-center">
          {/* Badge */}
          <div className="inline-flex items-center gap-2 glass-card px-4 py-2 rounded-full mb-8 animate-fade-in">
            <span className="w-2 h-2 rounded-full bg-success pulse-dot" />
            <span className="text-sm font-medium text-muted-foreground">
              Sistem IoT Real-time
            </span>
          </div>

          {/* Title */}
          <h1 className="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold mb-6 animate-fade-in delay-100">
            <span className="gradient-text">{siteData.hero.title.split(' ').slice(0, 2).join(' ')}</span>
            <br />
            <span className="text-foreground">{siteData.hero.title.split(' ').slice(2).join(' ')}</span>
          </h1>

          {/* Subtitle */}
          <p className="text-lg sm:text-xl text-muted-foreground max-w-2xl mx-auto mb-10 animate-fade-in delay-200">
            {siteData.hero.subtitle}
          </p>

          {/* CTAs */}
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16 animate-fade-in delay-300">
            <Button variant="gradient" size="xl" className="w-full sm:w-auto group">
              {siteData.hero.cta.primary}
              <ArrowRight className="w-5 h-5 transition-transform group-hover:translate-x-1" />
            </Button>
            <Button variant="hero" size="xl" className="w-full sm:w-auto group">
              <Play className="w-5 h-5" />
              {siteData.hero.cta.secondary}
            </Button>
          </div>

          {/* Stats */}
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 animate-fade-in delay-400">
            {siteData.hero.stats.map((stat, index) => (
              <div
                key={stat.label}
                className="glass-card rounded-2xl p-6 text-center hover:shadow-lg transition-all duration-300"
                style={{ animationDelay: `${(index + 4) * 100}ms` }}
              >
                <div className="flex items-center justify-center mb-3">
                  {iconMap[stat.label]}
                </div>
                <div className="text-2xl sm:text-3xl font-bold gradient-text mb-1">
                  {stat.value}
                </div>
                <div className="text-sm text-muted-foreground">
                  {stat.label}
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Floating Elements */}
        <div className="absolute -left-20 top-1/3 w-40 h-40 border border-primary/20 rounded-full float-animation opacity-50" />
        <div className="absolute -right-10 bottom-1/4 w-24 h-24 border border-secondary/20 rounded-full float-animation opacity-50" style={{ animationDelay: '2s' }} />
      </div>
    </section>
  );
}
