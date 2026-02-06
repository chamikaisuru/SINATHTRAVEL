
import { motion } from "framer-motion";
import { Users, Map, Clock, Award } from "lucide-react";

const stats = [
    {
        icon: <Users className="w-8 h-8 text-secondary" />,
        value: "5000+",
        label: "Happy Travelers",
    },
    {
        icon: <Map className="w-8 h-8 text-secondary" />,
        value: "50+",
        label: "Destinations",
    },
    {
        icon: <Clock className="w-8 h-8 text-secondary" />,
        value: "10+",
        label: "Years Experience",
    },
    {
        icon: <Award className="w-8 h-8 text-secondary" />,
        value: "20+",
        label: "Awards Won",
    },
];

export default function StatsSection() {
    return (
        <section className="bg-primary text-white py-16">
            <div className="container mx-auto px-4">
                <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
                    {stats.map((stat, index) => (
                        <motion.div
                            key={index}
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ delay: index * 0.1, duration: 0.5 }}
                            viewport={{ once: true }}
                            className="flex flex-col items-center text-center space-y-4"
                        >
                            <div className="bg-white/10 p-4 rounded-full backdrop-blur-sm">
                                {stat.icon}
                            </div>
                            <div>
                                <div className="text-4xl font-heading font-bold text-white mb-1">
                                    {stat.value}
                                </div>
                                <div className="text-primary-foreground/80 font-medium uppercase tracking-wider text-sm">
                                    {stat.label}
                                </div>
                            </div>
                        </motion.div>
                    ))}
                </div>
            </div>
        </section>
    );
}
