import { Quote } from 'lucide-react';
import siteData from '@/data/siteData.json';

export function TestimonialsSection() {
  return (
    <section id="testimonials" className="py-24 relative">
      <div className="container mx-auto px-4 sm:px-6">
        {/* Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="text-3xl sm:text-4xl md:text-5xl font-bold mb-4">
            <span className="gradient-text">{siteData.testimonials.title}</span>
          </h2>
          <p className="text-lg text-muted-foreground">
            {siteData.testimonials.subtitle}
          </p>
        </div>

        {/* Testimonials Grid */}
        <div className="grid md:grid-cols-3 gap-6 md:gap-8 max-w-6xl mx-auto">
          {siteData.testimonials.items.map((testimonial, index) => (
            <div
              key={testimonial.name}
              className="glass-card rounded-3xl p-8 relative overflow-hidden group hover:shadow-card transition-all duration-300"
            >
              {/* Quote Icon */}
              <div className="absolute top-6 right-6 text-primary/20 group-hover:text-primary/30 transition-colors">
                <Quote className="w-10 h-10" />
              </div>

              {/* Content */}
              <p className="text-foreground/80 leading-relaxed mb-8 relative z-10">
                "{testimonial.content}"
              </p>

              {/* Author */}
              <div className="flex items-center gap-4">
                {/* Avatar */}
                <div className="w-12 h-12 rounded-full gradient-btn flex items-center justify-center text-primary-foreground font-bold text-sm">
                  {testimonial.avatar}
                </div>

                {/* Info */}
                <div>
                  <div className="font-semibold text-foreground">
                    {testimonial.name}
                  </div>
                  <div className="text-sm text-muted-foreground">
                    {testimonial.role}
                  </div>
                </div>
              </div>

              {/* Decorative gradient */}
              <div className="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-secondary to-accent opacity-0 group-hover:opacity-100 transition-opacity" />
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
