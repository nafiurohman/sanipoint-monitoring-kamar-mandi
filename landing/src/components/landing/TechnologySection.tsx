import { Activity, Wind, Radio } from 'lucide-react';
import siteData from '@/data/siteData.json';

const sensorIcons: Record<string, React.ReactNode> = {
  'MQ-135': <Wind className="w-8 h-8" />,
  'IR Sensor': <Activity className="w-8 h-8" />,
  'RFID RC522': <Radio className="w-8 h-8" />,
};

const sensorColors: Record<string, string> = {
  'MQ-135': 'from-primary/20 to-primary/5 border-primary/30',
  'IR Sensor': 'from-accent/20 to-accent/5 border-accent/30',
  'RFID RC522': 'from-secondary/20 to-secondary/5 border-secondary/30',
};

const sensorTextColors: Record<string, string> = {
  'MQ-135': 'text-primary',
  'IR Sensor': 'text-accent',
  'RFID RC522': 'text-secondary',
};

export function TechnologySection() {
  return (
    <section id="technology" className="py-24 relative overflow-hidden">
      {/* Background */}
      <div className="absolute inset-0 bg-gradient-to-t from-muted/50 via-background to-background" />
      <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-primary/5 rounded-full blur-3xl" />

      <div className="container mx-auto px-4 sm:px-6 relative z-10">
        {/* Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="text-3xl sm:text-4xl md:text-5xl font-bold mb-4">
            <span className="gradient-text">{siteData.iotSensors.title}</span>
          </h2>
          <p className="text-lg text-muted-foreground">
            {siteData.iotSensors.subtitle}
          </p>
        </div>

        {/* Sensors Grid */}
        <div className="grid md:grid-cols-3 gap-6 md:gap-8 max-w-5xl mx-auto">
          {siteData.iotSensors.sensors.map((sensor, index) => (
            <div
              key={sensor.name}
              className={`sensor-card glass-card rounded-3xl p-8 border-2 bg-gradient-to-br ${sensorColors[sensor.name]} relative overflow-hidden group`}
            >
              {/* Status Indicator */}
              <div className="absolute top-6 right-6 flex items-center gap-2">
                <span className="w-2 h-2 rounded-full bg-success pulse-dot" />
                <span className="text-xs font-medium text-success uppercase">
                  {sensor.status}
                </span>
              </div>

              {/* Icon */}
              <div className={`mb-6 ${sensorTextColors[sensor.name]}`}>
                {sensorIcons[sensor.name]}
              </div>

              {/* Content */}
              <div className="mb-4">
                <span className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  {sensor.type}
                </span>
              </div>
              
              <h3 className="text-2xl font-bold mb-3 text-foreground">
                {sensor.name}
              </h3>
              
              <p className="text-muted-foreground text-sm leading-relaxed mb-6">
                {sensor.description}
              </p>

              {/* Unit Badge */}
              <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-background/50 border border-border/50">
                <span className="text-xs font-medium text-muted-foreground">Unit:</span>
                <span className={`text-sm font-bold ${sensorTextColors[sensor.name]}`}>
                  {sensor.unit}
                </span>
              </div>

              {/* Decorative Element */}
              <div className="absolute -bottom-8 -right-8 w-32 h-32 rounded-full border border-current opacity-10 group-hover:opacity-20 transition-opacity" />
            </div>
          ))}
        </div>

        {/* Connection Lines Decoration */}
        <div className="hidden lg:flex justify-center mt-12">
          <div className="flex items-center gap-4">
            <div className="w-24 h-px bg-gradient-to-r from-transparent via-primary/30 to-primary/50" />
            <div className="w-3 h-3 rounded-full border-2 border-primary bg-background" />
            <div className="w-32 h-px bg-gradient-to-r from-primary/50 via-secondary/50 to-accent/50" />
            <div className="w-3 h-3 rounded-full border-2 border-secondary bg-background" />
            <div className="w-32 h-px bg-gradient-to-r from-accent/50 via-secondary/50 to-primary/50" />
            <div className="w-3 h-3 rounded-full border-2 border-accent bg-background" />
            <div className="w-24 h-px bg-gradient-to-r from-accent/50 via-accent/30 to-transparent" />
          </div>
        </div>
      </div>
    </section>
  );
}
