import { ArrowDown } from 'lucide-react';
import siteData from '@/data/siteData.json';

export function HowItWorksSection() {
  return (
    <section id="how-it-works" className="py-24 relative">
      <div className="container mx-auto px-4 sm:px-6">
        {/* Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="text-3xl sm:text-4xl md:text-5xl font-bold mb-4">
            <span className="gradient-text">{siteData.howItWorks.title}</span>
          </h2>
          <p className="text-lg text-muted-foreground">
            {siteData.howItWorks.subtitle}
          </p>
        </div>

        {/* Steps */}
        <div className="max-w-4xl mx-auto">
          <div className="relative">
            {/* Vertical Line */}
            <div className="absolute left-8 md:left-1/2 top-0 bottom-0 w-px bg-gradient-to-b from-primary via-secondary to-accent hidden md:block" />

            {siteData.howItWorks.steps.map((step, index) => (
              <div
                key={step.number}
                className={`relative flex flex-col md:flex-row items-start md:items-center gap-6 md:gap-12 mb-12 last:mb-0 ${
                  index % 2 === 0 ? 'md:flex-row' : 'md:flex-row-reverse'
                }`}
              >
                {/* Step Content */}
                <div className={`flex-1 ${index % 2 === 0 ? 'md:text-right' : 'md:text-left'}`}>
                  <div className="glass-card rounded-2xl p-6 md:p-8 hover:shadow-card transition-all duration-300">
                    <h3 className="text-xl font-bold mb-2 text-foreground">
                      {step.title}
                    </h3>
                    <p className="text-muted-foreground">
                      {step.description}
                    </p>
                  </div>
                </div>

                {/* Step Number */}
                <div className="relative z-10 flex-shrink-0 order-first md:order-none">
                  <div className="w-16 h-16 rounded-2xl gradient-btn flex items-center justify-center text-primary-foreground font-bold text-xl">
                    {step.number}
                  </div>
                </div>

                {/* Empty space for alignment */}
                <div className="flex-1 hidden md:block" />

                {/* Arrow indicator (mobile) */}
                {index < siteData.howItWorks.steps.length - 1 && (
                  <div className="absolute left-8 -bottom-6 md:hidden">
                    <ArrowDown className="w-4 h-4 text-primary animate-bounce" />
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
